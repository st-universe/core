<?php

namespace Stu\Module\Spacecraft\Lib\Movement;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface ShipMoverInterface
{
    public function checkAndMove(
        SpacecraftWrapperInterface $leadWrapper,
        FlightRouteInterface $flightRoute
    ): MessageCollectionInterface;
}
