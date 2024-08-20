<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\LocationInterface;

interface MoveOnLayerInterface
{
    public function move(
        ShipWrapperInterface $wrapper,
        ?LocationInterface $target
    ): bool;
}
