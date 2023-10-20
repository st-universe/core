<?php

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;

interface ShipSystemDataFactoryInterface
{
    public function createSystemData(
        ShipSystemTypeEnum $systemType,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ): AbstractSystemData;
}
