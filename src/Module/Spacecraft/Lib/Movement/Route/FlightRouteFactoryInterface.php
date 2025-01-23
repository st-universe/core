<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

interface FlightRouteFactoryInterface
{
    public function getRouteForMapDestination(
        MapInterface|StarSystemMapInterface $destination,
        bool $isTranswarp = false
    ): FlightRouteInterface;

    public function getRouteForWormholeDestination(
        WormholeEntryInterface $destination,
        bool $isEntry
    ): FlightRouteInterface;

    public function getRouteForCoordinateDestination(
        SpacecraftInterface $spacecraft,
        int $x,
        int $y
    ): FlightRouteInterface;
}
