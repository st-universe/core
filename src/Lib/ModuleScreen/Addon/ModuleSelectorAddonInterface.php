<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Orm\Entity\ModuleInterface;

interface ModuleSelectorAddonInterface
{
    /** @return array<mixed> */
    public function getModificators(ModuleInterface $module): array;
}
