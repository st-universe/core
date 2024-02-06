<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface MoveOnLayerInterface
{
    public function move(
        ShipWrapperInterface $wrapper,
        MapInterface|StarSystemMapInterface|null $target
    ): bool;
}
