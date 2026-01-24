<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\Spacecraft;

interface SafeFlightRouteInterface
{
    public function getSafeFlightRoute(
        Spacecraft $spacecraft,
        callable $coordinateCallable
    ): ?FlightRouteInterface;
}
