<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Lib\ModuleScreen\ModuleSelectorAddonInterface;

interface ModuleSelectorAddonFactoryInterface
{
    public function createModuleSelectorAddon(int $moduleType): ?ModuleSelectorAddonInterface;
}
