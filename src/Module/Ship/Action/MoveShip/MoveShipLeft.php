<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

/**
 * Performs movement to the left
 */
final class MoveShipLeft extends AbstractDirectedMovement
{
    public const ACTION_IDENTIFIER = 'B_MOVE_LEFT';

    protected function isSanityCheckFaulty(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        return false;
    }

    protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();

        return $this->flightRouteFactory->getRouteForCoordinateDestination(
            $ship,
            max(1, $ship->getPosX() - $this->moveShipRequest->getFieldCount()),
            $ship->getPosY()
        );
    }
}
