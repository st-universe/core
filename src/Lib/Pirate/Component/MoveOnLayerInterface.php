<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;

interface MoveOnLayerInterface
{
    public function move(
        SpacecraftWrapperInterface $wrapper,
        ?Location $target
    ): bool;
}
