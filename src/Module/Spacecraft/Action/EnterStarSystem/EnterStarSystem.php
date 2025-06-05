<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\EnterStarSystem;

use Override;
use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class EnterStarSystem extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_ENTER_STARSYSTEM';

    #[Override]
    protected function isSanityCheckFaultyConcrete(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        $system = $ship->isOverSystem();
        return $system === null;
    }

    #[Override]
    protected function getFlightRoute(SpacecraftWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();

        $system = $ship->isOverSystem();
        if ($system === null) {
            throw new RuntimeException('should not happen');
        }

        $destination = $this->randomSystemEntry->getRandomEntryPoint($wrapper, $system);

        return $this->flightRouteFactory->getRouteForMapDestination($destination);
    }
}
