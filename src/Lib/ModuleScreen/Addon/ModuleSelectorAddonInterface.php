<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Orm\Entity\Module;

interface ModuleSelectorAddonInterface
{
    /** @return array<mixed> */
    public function getModificators(Module $module): array;
}
