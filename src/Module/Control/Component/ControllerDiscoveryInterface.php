<?php

namespace Stu\Module\Control\Component;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ControllerInterface;

interface ControllerDiscoveryInterface
{
    /**
     * @return array<string, ControllerInterface>
     */
    public function getControllers(ModuleEnum $module, bool $isViewController): array;
}
