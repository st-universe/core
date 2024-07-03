<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\MoveShip;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class MoveShip extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_MOVE';

    #[Override]
    protected function isSanityCheckFaultyConcrete(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        return false;
    }

    #[Override]
    protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface
    {
        return $this->flightRouteFactory->getRouteForCoordinateDestination(
            $wrapper->get(),
            $this->moveShipRequest->getDestinationPosX(),
            $this->moveShipRequest->getDestinationPosY()
        );
    }
}
