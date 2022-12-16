<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationShiplist;

use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Tal\OrbitShipItem;
use Stu\Module\Tal\OrbitShipItemInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShowStationShiplist implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_SHIPLIST';

    private ShipLoaderInterface $shipLoader;

    private ShowStationShiplistRequestInterface $showStationShiplistRequest;

    private StationUtilityInterface $stationUtility;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShowStationShiplistRequestInterface $showStationShiplistRequest,
        StationUtilityInterface $stationUtility
    ) {
        $this->shipLoader = $shipLoader;
        $this->showStationShiplistRequest = $showStationShiplistRequest;
        $this->stationUtility = $stationUtility;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            $this->showStationShiplistRequest->getStationId(),
            $userId
        );

        if (!$station->isBase()) {
            return;
        }

        $shipList = [];

        foreach ($this->stationUtility->getManageableShipList($station) as $entry) {
            $entry['ships'] = array_map(
                function (ShipInterface $ship) use ($game): OrbitShipItemInterface {
                    return new OrbitShipItem($ship, $game);
                },
                $entry['ships']
            );
            $shipList[] = $entry;
        }

        $game->setPageTitle(_('Angedockte Schiffe'));
        $game->setMacroInAjaxWindow('html/stationmacros.xhtml/shiplist');
        $game->setTemplateVar('SHIP', $station);
        $game->setTemplateVar('SHIP_LIST', $shipList);
        $game->setTemplateVar('CAN_UNDOCK', true);
    }
}
