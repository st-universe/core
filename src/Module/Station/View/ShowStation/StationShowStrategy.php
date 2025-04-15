<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStation;

use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ViewContext;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Spacecraft\View\ShowSpacecraft\SpacecraftTypeShowStragegyInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Entity\ConstructionProgressInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\StationShipRepairInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

final class StationShowStrategy implements SpacecraftTypeShowStragegyInterface
{
    public function __construct(
        private StationLoaderInterface $stationLoader,
        private StationShipRepairRepositoryInterface $stationShipRepairRepository,
        private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        private StationUtilityInterface $stationUtility,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private ColonyLibFactoryInterface $colonyLibFactory,
    ) {}

    #[Override]
    public function appendNavigationPart(GameControllerInterface $game): SpacecraftTypeShowStragegyInterface
    {
        $game->appendNavigationPart('station.php', _('Stationen'));

        return $this;
    }

    #[Override]
    public function setTemplateVariables(int $spacecraftId, GameControllerInterface $game): SpacecraftTypeShowStragegyInterface
    {
        $station = $this->stationLoader->getByIdAndUser(
            $spacecraftId,
            $game->getUser()->getId(),
            true,
            false
        );

        $game->setTemplateVar('STATION', $station);

        $progress =  $station->getConstructionProgress();
        if ($progress !== null && $progress->getRemainingTicks() !== 0) {
            $dockedWorkbees = $this->stationUtility->getDockedWorkbeeCount($station);
            $neededWorkbees = $this->stationUtility->getNeededWorkbeeCount($station, $station->getRump());

            $game->setTemplateVar('CONSTRUCTION_PROGRESS_WRAPPER', new ConstructionProgressWrapper(
                $progress,
                $station,
                $dockedWorkbees,
                $neededWorkbees
            ));
        }

        $this->doConstructionStuff($station, $progress, $game);
        $this->doStationStuff($station, $game);

        return $this;
    }

    private function doConstructionStuff(StationInterface $station, ?ConstructionProgressInterface $progress, GameControllerInterface $game): void
    {
        if (!$station->isConstruction()) {
            return;
        }

        if ($progress === null || $progress->getRemainingTicks() === 0) {
            $plans = $this->stationUtility->getStationBuildplansByUser($game->getUser()->getId());
            $game->setTemplateVar('POSSIBLE_STATIONS', $plans);

            $moduleSelectors = [];
            foreach ($plans as $plan) {
                $ms = $this->colonyLibFactory->createModuleSelector(
                    SpacecraftModuleTypeEnum::SPECIAL,
                    $station,
                    $plan->getRump(),
                    $game->getUser()
                );

                $moduleSelectors[] = $ms;
            }

            $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        }
    }

    private function doStationStuff(StationInterface $station, GameControllerInterface $game): void
    {
        if ($this->stationUtility->canManageShips($station)) {
            $game->setTemplateVar('CAN_MANAGE', true);
        }

        if ($this->stationUtility->canRepairShips($station)) {
            $game->setTemplateVar('CAN_REPAIR', true);

            $shipRepairProgress = array_map(
                fn(StationShipRepairInterface $repair): ShipWrapperInterface => $this->spacecraftWrapperFactory->wrapShip($repair->getShip()),
                $this->stationShipRepairRepository->getByStation(
                    $station->getId()
                )
            );

            $game->setTemplateVar('SHIP_REPAIR_PROGRESS', $shipRepairProgress);
        }

        if ($station->getRump()->getRoleId() === SpacecraftRumpEnum::SHIP_ROLE_SHIPYARD) {
            $game->setTemplateVar('SHIP_BUILD_PROGRESS', $this->shipyardShipQueueRepository->getByShipyard($station->getId()));
        }

        $firstOrbitShip = null;

        $dockedShips = $station->getDockedShips();
        if (!$dockedShips->isEmpty()) {
            // if selected, return the current target
            $target = request::postInt('target');

            if ($target !== 0) {
                foreach ($dockedShips as $ship) {
                    if ($ship->getId() === $target) {
                        $firstOrbitShip = $ship;
                    }
                }
            }
            if ($firstOrbitShip === null) {
                $firstOrbitShip = $dockedShips->first();
            }
        }

        $game->setTemplateVar('FIRST_MANAGE_SHIP', $firstOrbitShip !== null ? $this->spacecraftWrapperFactory->wrapShip($firstOrbitShip) : null);

        if ($station->getRump()->isShipyard()) {
            $game->setTemplateVar('AVAILABLE_BUILDPLANS', $this->stationUtility->getShipyardBuildplansByUser($game->getUser()->getId()));
        }
    }
    #[Override]
    public function getViewContext(): ViewContext
    {
        return new ViewContext(ModuleEnum::STATION, ShowSpacecraft::VIEW_IDENTIFIER);
    }
}
