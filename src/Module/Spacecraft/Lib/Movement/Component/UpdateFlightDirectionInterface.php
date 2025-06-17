<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use Stu\Component\Map\DirectionEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface UpdateFlightDirectionInterface
{
    public function updateWhenTraversing(
        LocationInterface $oldWaypoint,
        LocationInterface $waypoint,
        SpacecraftWrapperInterface $wrapper
    ): DirectionEnum;

    public function updateWhenSystemExit(
        SpacecraftWrapperInterface $wrapper,
        StarSystemMapInterface $starsystemMap
    ): void;
}
