<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

final class MyWrapper
{
    private $modSels = null;

    public function register($modSel)
    {
        $this->modSels[$modSel->getModuleType()] = $modSel;
    }

    public function __get($moduleType): ModuleSelectorInterface
    {
        return $this->modSels[$moduleType];
    }
}
