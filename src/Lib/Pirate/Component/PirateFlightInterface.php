<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface PirateFlightInterface
{
    public function movePirate(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute
    ): void;
}
