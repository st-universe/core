<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Component\Ship\ShipModuleTypeEnum;

interface ModuleSelectorAddonFactoryInterface
{
    public function createModuleSelectorAddon(ShipModuleTypeEnum $moduleType): ?ModuleSelectorAddonInterface;
}
