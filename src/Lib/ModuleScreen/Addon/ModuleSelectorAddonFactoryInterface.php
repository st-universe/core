<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;

interface ModuleSelectorAddonFactoryInterface
{
    public function createModuleSelectorAddon(SpacecraftModuleTypeEnum $moduleType): ?ModuleSelectorAddonInterface;
}
