<?php

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;

interface ShipTorpedoManagerInterface
{
    public function changeTorpedo(
        SpacecraftWrapperInterface $wrapper,
        int $changeAmount,
        ?TorpedoTypeInterface $type = null
    ): void;
}
