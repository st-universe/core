<?php

namespace Stu\Module\Ship\Lib\Torpedo;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;

interface ShipTorpedoManagerInterface
{
    public function changeTorpedo(
        ShipWrapperInterface $wrapper,
        int $changeAmount,
        ?TorpedoTypeInterface $type = null
    ): void;
}
