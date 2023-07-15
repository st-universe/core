<?php

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ShipMoverInterface
{
    public function checkAndMove(
        ShipWrapperInterface $leadShip,
        FlightRouteInterface $flightRoute
    ): InformationWrapper;
}
