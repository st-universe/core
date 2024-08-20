<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

interface EnterWaypointInterface
{
    public function enterNextWaypoint(
        ShipInterface $ship,
        bool $isTraversing,
        LocationInterface $waypoint,
        ?WormholeEntryInterface $wormholeEntry
    ): void;
}
