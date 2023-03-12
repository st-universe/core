<?php

namespace Stu\Module\Ship\Lib\Torpedo;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ClearTorpedoInterface
{
    public function clearTorpedoStorage(ShipWrapperInterface $wrapper): void;
}
