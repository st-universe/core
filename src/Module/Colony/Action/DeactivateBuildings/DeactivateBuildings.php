<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeactivateBuildings;

use request;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\BuildingMassActionConfigurationInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class DeactivateBuildings implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MULTIPLE_DEACTIVATION';

    private ColonyLoaderInterface $colonyLoader;

    private BuildingActionInterface $buildingAction;

    private CommodityRepositoryInterface $commodityRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private BuildingMassActionConfigurationInterface $buildingMassActionConfiguration;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingActionInterface $buildingAction,
        CommodityRepositoryInterface $commodityRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingMassActionConfigurationInterface $buildingMassActionConfiguration
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingAction = $buildingAction;
        $this->commodityRepository = $commodityRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingMassActionConfiguration = $buildingMassActionConfiguration;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $colonyId = (int) $colony->getId();

        $mode = request::indInt('mode');
        $selection = request::getvars()['selection'] ?? request::postvars()['selection'] ?? null;

        $config = $this->buildingMassActionConfiguration->getConfigurations()[$mode] ?? null;

        if ($config === null) {
            return;
        }

        /** @var PlanetFieldInterface[] $fields */
        $fields = $config($colony, $selection);

        foreach ($fields as $field) {
            if (!$field->isActive()) {
                continue;
            }
            $this->buildingAction->deactivate($colony, $field, $game);
        }

        $list = $this->planetFieldRepository->getByColonyWithBuilding($colonyId);

        usort(
            $list,
            function (PlanetFieldInterface $a, PlanetFieldInterface $b): int {
                return strcmp($a->getBuilding()->getName(), $b->getBuilding()->getName());
            }
        );

        $game->setTemplateVar('BUILDING_LIST', $list);
        $game->setTemplateVar('USEABLE_COMMODITY_LIST', $this->commodityRepository->getByBuildingsOnColony($colonyId));

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU' => ColonyEnum::MENU_BUILDINGS]);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
