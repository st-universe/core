<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

/**
 * Performs movement to the left
 */
final class MoveShipLeft extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_MOVE_LEFT';

    #[Override]
    protected function isSanityCheckFaultyConcrete(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        return false;
    }

    #[Override]
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
