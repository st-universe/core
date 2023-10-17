<?php

namespace Stu\Module\Ship\Lib\Auxiliary;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ShipShutdownInterface
{
    public function shutdown(ShipWrapperInterface $wrapper): void;
}
