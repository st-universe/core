<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use JBBCode\Parser;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\Type\AstroLaboratoryShipSystem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\PassiveRepairProjectionCalculatorInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\StationShipRepairRepositoryInterface;

class StateIconAndTitle
{
    public function __construct(
        private GameControllerInterface $game,
        private Parser $bbCodeParser,
        private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        private StationShipRepairRepositoryInterface $stationShipRepairRepository,
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        private PassiveRepairProjectionCalculatorInterface $passiveRepairProjectionCalculator,
        private StuTime $stuTime
    ) {}

    /**
     * @return array<string>|null
     */
    public function getStateIconAndTitle(SpacecraftWrapperInterface $wrapper): ?array
    {
        $spacecraft = $wrapper->get();
        $state = $spacecraft->getState();

        return match ($state) {
            SpacecraftStateEnum::RETROFIT => ['buttons/konstr1', 'Schiff wird umgerüstet'],
            SpacecraftStateEnum::REPAIR_ACTIVE => $this->getForActiveRepair($spacecraft),
            SpacecraftStateEnum::REPAIR_PASSIVE => $this->getForActivePassive($wrapper),
            SpacecraftStateEnum::ASTRO_FINALIZING => $this->getForAstroFinalizing($wrapper),
            SpacecraftStateEnum::ACTIVE_TAKEOVER => $this->getForActiveTakeover($wrapper),
            SpacecraftStateEnum::GATHER_RESOURCES => $this->getForGatherResources($spacecraft),
            default => $this->getForPassiveTakeover($wrapper)
        };
    }

    /** @return array<string> */
    private function getForActiveRepair(Spacecraft $spacecraft): array
    {
        $isStation = $spacecraft->isStation();
        return ['buttons/rep2', sprintf(
            '%s repariert %s',
            $isStation ? 'Stationscrew' : 'Schiffscrew',
            $isStation ? 'die Station' : 'das Schiff',
        )];
    }

    /** @return array<string> */
    private function getForActivePassive(SpacecraftWrapperInterface $wrapper): array
    {
        $shipRepairTitle = $this->getPassiveShipRepairTitle($wrapper->get());
        if ($shipRepairTitle !== null) {
            return ['buttons/rep2', $shipRepairTitle];
        }

        return ['buttons/rep2', sprintf(
            '%s wird repariert (noch %d Runden)',
            $wrapper->get()->isStation() ? 'Station' : 'Schiff',
            $wrapper->getRepairDuration()
        )];
    }

    private function getPassiveShipRepairTitle(Spacecraft $spacecraft): ?string
    {
        if (!$spacecraft instanceof Ship) {
            return null;
        }

        $shipId = $spacecraft->getId();
        $colonyRepair = $this->colonyShipRepairRepository->getByShip($shipId);
        if ($colonyRepair !== null) {
            $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction(
                $colonyRepair->getColony(),
                BuildingFunctionEnum::REPAIR_SHIPYARD
            );

            return $this->formatPassiveShipRepairTitle(
                $this->passiveRepairProjectionCalculator->getPotentialFinishTime(
                    $this->colonyShipRepairRepository->getByColonyField(
                        $colonyRepair->getColonyId(),
                        $colonyRepair->getFieldId()
                    ),
                    $isRepairStationBonus ? 2 : 1,
                    $isRepairStationBonus,
                    $shipId
                )
            );
        }

        $stationRepair = $this->stationShipRepairRepository->getByShip($shipId);
        if ($stationRepair === null) {
            return $this->formatPassiveShipRepairTitle(0);
        }

        return $this->formatPassiveShipRepairTitle(
            $this->passiveRepairProjectionCalculator->getPotentialFinishTime(
                $this->stationShipRepairRepository->getByStation($stationRepair->getStationId()),
                1,
                false,
                $shipId
            )
        );
    }

    private function formatPassiveShipRepairTitle(int $potentialFinishTime): string
    {
        if ($potentialFinishTime <= 0) {
            return 'Schiff wird repariert (Fertigstellung ausstehend)';
        }

        return sprintf(
            'Schiff wird repariert (voraussichtliche Fertigstellung: %s)',
            $this->stuTime->transformToStuDateTime($potentialFinishTime)
        );
    }

    /** @return array<string>|null */
    private function getForAstroFinalizing(SpacecraftWrapperInterface $wrapper): ?array
    {
        $astroLab = $wrapper instanceof ShipWrapperInterface ? $wrapper->getAstroLaboratorySystemData() : null;
        if ($astroLab === null) {
            return null;
        }

        return ['buttons/map1', sprintf(
            'Schiff kartographiert (noch %d Runden)',
            $astroLab->getAstroStartTurn() + AstroLaboratoryShipSystem::TURNS_TO_FINISH - $this->game->getCurrentRound()->getTurn()
        )];
    }

    /** @return array<string>|null */
    private function getForActiveTakeover(SpacecraftWrapperInterface $wrapper): ?array
    {
        $takeover = $wrapper->get()->getTakeoverActive();
        if ($takeover === null) {
            return null;
        }

        return ['buttons/take2', sprintf(
            'Schiff übernimmt die "%s" (noch %d Runden)',
            $this->bbCodeParser->parse($takeover->getTargetSpacecraft()->getName())->getAsText(),
            $wrapper->getTakeoverTicksLeft($takeover)
        )];
    }

    /** @return array<string>|null */
    private function getForGatherResources(Spacecraft $spacecraft): ?array
    {
        if (!$spacecraft instanceof Ship) {
            return null;
        }

        $miningqueue = $spacecraft->getMiningQueue();
        $module = $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR)->getModule();
        if ($miningqueue === null || $module === null) {
            return null;
        }

        $locationmining = $miningqueue->getLocationMining();
        $gathercount = $module->getFactionId() == null ? 100 : 200;

        return [sprintf('commodities/%s', $locationmining->getCommodity()->getId()), sprintf(
            'Schiff sammelt Ressourcen (~%d %s/Tick)',
            $gathercount,
            $locationmining->getCommodity()->getName()
        )];
    }

    /** @return array<string>|null */
    private function getForPassiveTakeover(SpacecraftWrapperInterface $wrapper): ?array
    {
        $takeover = $wrapper->get()->getTakeoverPassive();
        if ($takeover === null) {
            return null;
        }

        $sourceUserNamePlainText = $this->bbCodeParser->parse($takeover->getSourceSpacecraft()->getUser()->getName())->getAsText();
        return ['buttons/untake2', sprintf(
            'Schiff wird von Spieler "%s" übernommen (noch %d Runden)',
            $sourceUserNamePlainText,
            $wrapper->getTakeoverTicksLeft($takeover)
        )];
    }
}
