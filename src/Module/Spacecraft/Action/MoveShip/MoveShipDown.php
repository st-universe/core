<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\MoveShip;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

/**
 * Performs downwards movement
 */
final class MoveShipDown extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_MOVE_DOWN';

    #[\Override]
    protected function isSanityCheckFaultyConcrete(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        return false;
    }

    #[\Override]
    protected function getFlightRoute(SpacecraftWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();

        return $this->flightRouteFactory->getRouteForCoordinateDestination(
            $ship,
            $ship->getPosX(),
            $ship->getPosY() + $this->moveShipRequest->getFieldCount()
        );
    }
}
