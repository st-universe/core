<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\MoveShip;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class MoveShip extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_MOVE';

    #[Override]
    protected function isSanityCheckFaultyConcrete(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        return false;
    }

    #[Override]
    protected function getFlightRoute(SpacecraftWrapperInterface $wrapper): FlightRouteInterface
    {
        return $this->flightRouteFactory->getRouteForCoordinateDestination(
            $wrapper->get(),
            $this->moveShipRequest->getDestinationPosX(),
            $this->moveShipRequest->getDestinationPosY()
        );
    }
}
