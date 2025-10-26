<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\MoveShip;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

/**
 * Performs upwards movement
 */
final class MoveShipUp extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_MOVE_UP';

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
            max(1, $ship->getPosY() - $this->moveShipRequest->getFieldCount())
        );
    }
}
