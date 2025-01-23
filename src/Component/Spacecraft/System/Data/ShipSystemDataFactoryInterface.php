<?php

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;

interface ShipSystemDataFactoryInterface
{
    public function createSystemData(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ): AbstractSystemData;
}
