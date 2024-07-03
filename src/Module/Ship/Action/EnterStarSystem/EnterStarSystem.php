<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EnterStarSystem;

use Override;
use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class EnterStarSystem extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_ENTER_STARSYSTEM';

    #[Override]
    protected function isSanityCheckFaultyConcrete(ShipWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        $system = $ship->isOverSystem();
        return $system === null;
    }

    #[Override]
    protected function getFlightRoute(ShipWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();

        $system = $ship->isOverSystem();
        if ($system === null) {
            throw new RuntimeException('should not happen');
        }

        $destination = $this->randomSystemEntry->getRandomEntryPoint($ship, $system);

        return $this->flightRouteFactory->getRouteForMapDestination($destination);
    }
}
