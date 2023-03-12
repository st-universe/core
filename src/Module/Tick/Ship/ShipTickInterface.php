<?php

namespace Stu\Module\Tick\Ship;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ShipTickInterface
{
    public function work(ShipWrapperInterface $wrapper): void;
}
