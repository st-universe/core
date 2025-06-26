<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\WormholeEntry;

interface FlightRouteFactoryInterface
{
    public function getRouteForMapDestination(
        Map|StarSystemMap $destination,
        bool $isTranswarp = false
    ): FlightRouteInterface;

    public function getRouteForWormholeDestination(
        WormholeEntry $destination,
        bool $isEntry
    ): FlightRouteInterface;

    public function getRouteForCoordinateDestination(
        Spacecraft $spacecraft,
        int $x,
        int $y
    ): FlightRouteInterface;
}
