<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use Stu\Component\Map\DirectionEnum;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;

interface SpacecraftMovementPublisherInterface
{
    public function publishMovement(
        Spacecraft $spacecraft,
        DirectionEnum $direction,
        Location $currentLocation,
        Location $nextLocation
    ): void;

    public function publishRemoval(Spacecraft $spacecraft): void;

    public function publishState(Spacecraft $spacecraft): void;
}
