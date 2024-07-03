<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EnterWormhole;

use Override;
use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class EnterWormhole extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_ENTER_WORMHOLE';

    #[Override]
    protected function isSanityCheckFaultyConcrete(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        $map = $ship->getMap();

        if ($map === null) {
            return true;
        }

        $wormholeEntry = $map->getRandomWormholeEntry();
        if ($wormholeEntry === null) {
            return true;
        }

        if ($ship->isWarped()) {
            return true;
        }
        return $ship->isBase();
    }

    #[Override]
    protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();

        $map = $ship->getMap();

        if ($map === null) {
            throw new RuntimeException('should not happen');
        }

        $wormholeEntry = $map->getRandomWormholeEntry();
        if ($wormholeEntry === null) {
            throw new RuntimeException('should not happen');
        }

        return $this->flightRouteFactory->getRouteForWormholeDestination($wormholeEntry, true);
    }
}
