<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Orm\Entity\ShipInterface;

interface SafeFlightRouteInterface
{
    public function getSafeFlightRoute(
        ShipInterface $ship,
        callable $coordinateCallable
    ): ?FlightRouteInterface;
}
