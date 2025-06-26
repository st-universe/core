<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Location;

interface MoveOnLayerInterface
{
    public function move(
        ShipWrapperInterface $wrapper,
        ?Location $target
    ): bool;
}
