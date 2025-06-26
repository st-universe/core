<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\WormholeEntry;

interface EnterWaypointInterface
{
    public function enterNextWaypoint(
        ?Spacecraft $spacecraft,
        bool $isTraversing,
        Location $waypoint,
        ?WormholeEntry $wormholeEntry
    ): void;
}
