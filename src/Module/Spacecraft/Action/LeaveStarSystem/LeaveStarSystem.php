<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\LeaveStarSystem;

use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Action\MoveShip\AbstractDirectedMovement;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class LeaveStarSystem extends AbstractDirectedMovement
{
    public const string ACTION_IDENTIFIER = 'B_LEAVE_STARSYSTEM';

    #[\Override]
    protected function isSanityCheckFaultyConcrete(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        $starsystemMap = $ship->getStarsystemMap();
        if ($starsystemMap === null) {
            return true;
        }

        //the destination map field
        $outerMap = $starsystemMap->getSystem()->getMap();
        return $outerMap === null;
    }

    #[\Override]
    protected function getFlightRoute(SpacecraftWrapperInterface $wrapper): FlightRouteInterface
    {
        $ship = $wrapper->get();

        $starsystemMap = $ship->getStarsystemMap();
        if ($starsystemMap === null) {
            throw new RuntimeException('should not happen');
        }

        //the destination map field
        $destination = $starsystemMap->getSystem()->getMap();
        if ($destination === null) {
            throw new RuntimeException('should not happen');
        }

        return $this->flightRouteFactory->getRouteForMapDestination($destination);
    }
}
