<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateBuildingsGoodProducer;

use Colfields;
use Good;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class ActivateBuildingsGoodProducer implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_ACTIVATE_GOODRELATED_PROD';

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

        $goodId = request::postIntFatal('good');

        $fields = Colfields::getListBy(
            "colonies_id=" . $colonyId . " AND buildings_id>0 AND aktiv=0 AND buildings_id IN (SELECT buildings_id FROM stu_buildings_goods WHERE goods_id=" . $goodId . " AND count>0)"
        );

        foreach ($fields as $field) {
            $this->buildingAction->activate($colony, $field, $game);
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
