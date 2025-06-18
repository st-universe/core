<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use Stu\Component\Map\DirectionEnum;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface UpdateFlightDirectionInterface
{
    public function updateWhenTraversing(
        LocationInterface $oldWaypoint,
        LocationInterface $waypoint,
        SpacecraftInterface $ship
    ): DirectionEnum;

    public function updateWhenSystemExit(
        SpacecraftInterface $ship,
        StarSystemMapInterface $starsystemMap
    ): void;
}
