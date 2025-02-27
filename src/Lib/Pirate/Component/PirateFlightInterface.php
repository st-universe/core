<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface PirateFlightInterface
{
    public function movePirate(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute
    ): void;
}
