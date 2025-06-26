<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use Stu\Component\Map\DirectionEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\StarSystemMap;

interface UpdateFlightDirectionInterface
{
    public function updateWhenTraversing(
        Location $oldWaypoint,
        Location $waypoint,
        SpacecraftWrapperInterface $wrapper
    ): DirectionEnum;

    public function updateWhenSystemExit(
        SpacecraftWrapperInterface $wrapper,
        StarSystemMap $starsystemMap
    ): void;
}
