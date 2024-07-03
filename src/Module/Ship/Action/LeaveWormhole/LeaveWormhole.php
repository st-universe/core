<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveWormhole;

use Override;
use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class LeaveWormhole extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_LEAVE_WORMHOLE';

    #[Override]
    protected function isSanityCheckFaultyConcrete(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        $starsystemMap = $ship->getStarsystemMap();
        if ($starsystemMap === null) {
            return true;
        }

        if (!$starsystemMap->getSystem()->isWormhole()) {
            return true;
        }

        $wormholeEntry = $starsystemMap->getRandomWormholeEntry();
        return $wormholeEntry === null;
    }

    #[Override]
    protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();

        $starsystemMap = $ship->getStarsystemMap();
        if ($starsystemMap === null) {
            throw new RuntimeException('should not happen');
        }

        $wormholeEntry = $starsystemMap->getRandomWormholeEntry();
        if ($wormholeEntry === null) {
            throw new RuntimeException('should not happen');
        }

        return $this->flightRouteFactory->getRouteForWormholeDestination($wormholeEntry, false);
    }
}
