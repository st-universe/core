<?php

namespace Stu\Component\Ship\System\Data;

use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;

interface ShipSystemDataFactoryInterface
{
    public function createSystemData(
        int $systemType,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ): AbstractSystemData;
}
