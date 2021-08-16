<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShipManagement;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\OrbitFleetItemInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class ShowShipManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_MANAGEMENT';

    private ShipLoaderInterface $shipLoader;

    private ShowShipManagementRequestInterface $showShipManagementRequest;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShowShipManagementRequestInterface $showShipManagementRequest,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->showShipManagementRequest = $showShipManagementRequest;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser($this->showShipManagementRequest->getStationId(), $userId);

        if (!$station->isBase()) {
            return;
        }

        $shipList = $station->getDockedShips();

        $groupedList = [];

        foreach ($shipList as $ship) {
            if ($ship === $station) {
                continue;
            }
            if ($ship->getWarpState()) {
                continue;
            }
            $fleetId = $ship->getFleetId();

            $fleet = $groupedList[$fleetId] ?? null;
            if ($fleet === null) {
                $groupedList[$fleetId] = [];
            }

            $groupedList[$fleetId][] = $this->colonyLibFactory->createOrbitShipItem($ship, $userId);
        }

        $list = [];

        foreach ($groupedList as $fleetId => $shipList) {
            $list[] = $this->colonyLibFactory->createOrbitFleetItem(
                (int) $fleetId,
                $shipList,
                $userId
            );
        }

        usort(
            $list,
            function (OrbitFleetItemInterface $a, OrbitFleetItemInterface $b): int {
                return $b->getSort() <=> $a->getSort();
            }
        );

        $game->appendNavigationPart(
            'station.php',
            _('Stationen')
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%s',
                ShowShip::VIEW_IDENTIFIER,
                $station->getId()
            ),
            $station->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%d',
                static::VIEW_IDENTIFIER,
                $station->getId()
            ),
            _('Schiffsmanagement')
        );
        $game->setPagetitle(sprintf('%s Bereich', $station->getName()));
        $game->setTemplateFile('html/stationshipmanagement.xhtml');

        $game->setTemplateVar('STATION', $station);
        $game->setTemplateVar('SHIP_LIST', $list);
    }
}
