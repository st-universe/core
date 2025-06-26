<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\Ship;

interface SafeFlightRouteInterface
{
    public function getSafeFlightRoute(
        Ship $ship,
        callable $coordinateCallable
    ): ?FlightRouteInterface;
}
