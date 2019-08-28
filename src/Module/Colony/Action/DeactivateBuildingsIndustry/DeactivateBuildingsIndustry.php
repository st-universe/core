<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeactivateBuildingsIndustry;

use Colfields;
use Good;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class DeactivateBuildingsIndustry implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_WORKRELATED';

    private $colonyLoader;

    private $buildingAction;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingActionInterface $buildingAction
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingAction = $buildingAction;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $colonyId = $colony->getId();

        $fields = Colfields::getListBy("colonies_id=" . $colonyId . " AND buildings_id>0 AND aktiv=1 AND buildings_id IN (SELECT id FROM stu_buildings WHERE bev_use>0)");

        foreach ($fields as $field) {
            $this->buildingAction->deactivate($colony, $field, $game);
        }

        $list = Colfields::getListBy('colonies_id=' . $colony->getId() . ' AND buildings_id>0');
        usort($list, 'compareBuildings');

        $game->setTemplateVar('BUILDING_LIST', $list);
        $game->setTemplateVar('USEABLE_GOOD_LIST', Good::getListByActiveBuildings($colony->getId()));

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU' => MENU_BUILDINGS]);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
