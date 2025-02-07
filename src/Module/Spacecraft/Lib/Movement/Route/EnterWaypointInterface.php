<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

interface EnterWaypointInterface
{
    public function enterNextWaypoint(
        ?SpacecraftInterface $spacecraft,
        bool $isTraversing,
        LocationInterface $waypoint,
        ?WormholeEntryInterface $wormholeEntry
    ): void;
}
