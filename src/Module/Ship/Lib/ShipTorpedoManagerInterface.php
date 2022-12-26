<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\TorpedoTypeInterface;

interface ShipTorpedoManagerInterface
{
    public function changeTorpedo(
        ShipWrapperInterface $wrapper,
        int $changeAmount,
        TorpedoTypeInterface $type = null
    );

    public function removeTorpedo(ShipWrapperInterface $wrapper);
}
