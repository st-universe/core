<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Torpedo;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\TorpedoType;

interface ShipTorpedoManagerInterface
{
    public function changeTorpedo(
        SpacecraftWrapperInterface $wrapper,
        int $changeAmount,
        ?TorpedoType $type = null
    ): void;
}
