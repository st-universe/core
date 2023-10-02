<?php

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ShipMoverInterface
{
    public function checkAndMove(
        ShipWrapperInterface $leadShip,
        FlightRouteInterface $flightRoute
    ): MessageCollectionInterface;
}
