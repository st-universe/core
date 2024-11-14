<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShipManagement;

use Override;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

final class ShowShipManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_MANAGEMENT';

    public function __construct(private ShipLoaderInterface $shipLoader, private ShowShipManagementRequestInterface $showShipManagementRequest, private ShipWrapperFactoryInterface $shipWrapperFactory, private StationUtilityInterface $stationUtility)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            $this->showShipManagementRequest->getStationId(),
            $userId,
            false,
            false
        );

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
            if ($ship->isWarped()) {
                continue;
            }
            $fleetId = $ship->getFleetId() ?? 0;

            $fleet = $groupedList[$fleetId] ?? null;
            if ($fleet === null) {
                $groupedList[$fleetId] = [];
            }

            $groupedList[$fleetId][] = $ship;
        }

        $list = [];

        foreach ($groupedList as $fleetId => $shipList) {
            usort(
                $shipList,
                function (ShipInterface $a, ShipInterface $b): int {
                    if ($b->isFleetLeader() === $a->isFleetLeader()) {
                        $catA = $a->getRump()->getCategoryId();
                        $catB = $b->getRump()->getCategoryId();
                        if ($catB === $catA) {
                            $roleA = $a->getRump()->getRoleId();
                            $roleB = $b->getRump()->getRoleId();
                            if ($roleB === $roleA) {
                                if ($b->getRumpId() === $a->getRumpId()) {
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

            $fleetWrapper = $this->shipWrapperFactory->wrapShipsAsFleet($shipList, $fleetId === 0);
            $key = sprintf('%d.%d', $fleetWrapper->get()->getSort(), $fleetWrapper->get()->getUser()->getId());
            $list[$key] = $fleetWrapper;
        }

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
                self::VIEW_IDENTIFIER,
                $station->getId()
            ),
            _('Schiffsmanagement')
        );
        $game->setPagetitle(sprintf('%s Bereich', $station->getName()));
        $game->setViewTemplate('html/station/shipManagement.twig');

        $game->setTemplateVar('MANAGER_ID', $station->getId());
        $game->setTemplateVar('FLEET_WRAPPERS', $list);
    }
}
