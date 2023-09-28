<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

interface EnterWaypointInterface
{
    public function enterNextWaypoint(
        ShipInterface $ship,
        bool $isTraversing,
        MapInterface|StarSystemMapInterface $waypoint,
        ?WormholeEntryInterface $wormholeEntry
    ): void;
}
