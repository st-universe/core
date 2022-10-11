<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShipManagement;

use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\OrbitFleetItemInterface;
use Stu\Module\Colony\Lib\OrbitManagementShipItem;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class ShowShipManagement implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_MANAGEMENT';

    private ShipLoaderInterface $shipLoader;

    private ShowShipManagementRequestInterface $showShipManagementRequest;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private StationUtilityInterface $stationUtility;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShowShipManagementRequestInterface $showShipManagementRequest,
        ColonyLibFactoryInterface $colonyLibFactory,
        StationUtilityInterface $stationUtility
    ) {
        $this->shipLoader = $shipLoader;
        $this->showShipManagementRequest = $showShipManagementRequest;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->stationUtility = $stationUtility;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser($this->showShipManagementRequest->getStationId(), $userId);

        if (!$station->isBase()) {
            return;
        }

        if (!$this->stationUtility->canManageShips($station)) {
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

            $groupedList[$fleetId][] = $this->colonyLibFactory->createOrbitManagementShipItem($ship, $userId);
        }

        $list = [];

        foreach ($groupedList as $fleetId => $shipList) {
            usort(
                $shipList,
                function (OrbitManagementShipItem $itemA, OrbitManagementShipItem $itemB): int {
                    $a = $itemA->getShip();
                    $b = $itemB->getShip();

                    if ($b->isFleetLeader() == $a->isFleetLeader()) {
                        $catA = $a->getRump()->getCategoryId();
                        $catB = $b->getRump()->getCategoryId();
                        if ($catB == $catA) {
                            $roleA = $a->getRump()->getRoleId();
                            $roleB = $b->getRump()->getRoleId();
                            if ($roleB == $roleA) {
                                if ($b->getRumpId() == $a->getRumpId()) {

                                    return $a->getName() <=> $b->getName();
                                }

                                return $b->getRumpId() <=> $a->getRumpId();
                            }

                            return $roleB <=> $roleA;
                        }
                        return $catB <=> $catA;
                    }
                    return $b->isFleetLeader() <=> $a->isFleetLeader();
                }
            );

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
