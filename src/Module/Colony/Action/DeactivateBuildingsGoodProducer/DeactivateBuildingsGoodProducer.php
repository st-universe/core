<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeactivateBuildingsGoodProducer;

use Colfields;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class DeactivateBuildingsGoodProducer implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_GOODRELATED_PROD';

    private $colonyLoader;

    private $buildingAction;

    private $commodityRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingActionInterface $buildingAction,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingAction = $buildingAction;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $colonyId = (int) $colony->getId();

        $goodId = request::postIntFatal('good');

        $fields = Colfields::getListBy(
            "colonies_id=" . $colonyId . " AND buildings_id>0 AND aktiv=1 AND buildings_id IN (SELECT buildings_id FROM stu_buildings_goods WHERE goods_id=" . $goodId . " AND count>0)"
        );

        foreach ($fields as $field) {
            $this->buildingAction->deactivate($colony, $field, $game);
        }

        $list = Colfields::getListBy('colonies_id=' . $colony->getId() . ' AND buildings_id>0');
        usort($list, 'compareBuildings');

        $game->setTemplateVar('BUILDING_LIST', $list);
        $game->setTemplateVar('USEABLE_GOOD_LIST', $this->commodityRepository->getByBuildingsOnColony($colonyId));

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU' => MENU_BUILDINGS]);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
