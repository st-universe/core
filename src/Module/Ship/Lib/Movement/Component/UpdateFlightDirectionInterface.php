<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface UpdateFlightDirectionInterface
{
    public function updateWhenTraversing(
        LocationInterface $oldWaypoint,
        LocationInterface $waypoint,
        ShipInterface $ship
    ): int;

    public function updateWhenSystemExit(
        ShipInterface $ship,
        StarSystemMapInterface $starsystemMap
    ): void;
}
