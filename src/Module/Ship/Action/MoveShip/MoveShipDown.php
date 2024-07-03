<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

/**
 * Performs downwards movement
 */
final class MoveShipDown extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_MOVE_DOWN';

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
            $ship->getPosX(),
            $ship->getPosY() + $this->moveShipRequest->getFieldCount()
        );
    }
}
