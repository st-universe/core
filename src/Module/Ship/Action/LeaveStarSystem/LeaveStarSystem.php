<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveStarSystem;

use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class LeaveStarSystem extends AbstractDirectedMovement
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_STARSYSTEM';

    protected function isSanityCheckFaultyConcrete(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        $starsystemMap = $ship->getStarsystemMap();
        if ($starsystemMap === null) {
            return true;
        }

        //the destination map field
        $outerMap = $starsystemMap->getSystem()->getMapField();
        if ($outerMap === null) {
            return true;
        }

        if (
            $ship->isFleetLeader()
            && $ship->getFleet() !== null
            && $ship->getFleet()->getDefendedColony() !== null
        ) {
            $game->addInformation(_('Verlassen des Systems während Kolonie-Verteidigung nicht möglich'));
            return true;
        }

        if (
            $ship->isFleetLeader()
            && $ship->getFleet() !== null
            && $ship->getFleet()->getBlockedColony() !== null
        ) {
            $game->addInformation(_('Verlassen des Systems während Kolonie-Blockierung nicht möglich'));
            return true;
        }

        return false;
    }

    protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();

        $starsystemMap = $ship->getStarsystemMap();
        if ($starsystemMap === null) {
            throw new RuntimeException('should not happen');
        }

        //the destination map field
        $destination = $starsystemMap->getSystem()->getMapField();
        if ($destination === null) {
            throw new RuntimeException('should not happen');
        }

        return $this->flightRouteFactory->getRouteForMapDestination($destination);
    }
}
