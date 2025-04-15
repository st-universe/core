<?php

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;
use Stu\Config\Init;

class ControllerDiscovery
{
    /**
     * @return array<string, ControllerInterface>
     */
    public function getControllers(ModuleEnum $module, bool $isViewController): array
    {
        $controllers = Init::getContainer()->get($this->getContainerIdentifier($module->name, $isViewController));

        $commonModule = $module->getCommonModule();
        if ($commonModule === null) {
            return $controllers;
        }

        return array_merge(Init::getContainer()->get($this->getContainerIdentifier($commonModule, $isViewController)), $controllers);
    }

    private function getContainerIdentifier(string $module, bool $isViewController): string
    {
        return sprintf(
            '%s_%s',
            $module,
            $isViewController ? 'VIEWS' : 'ACTIONS'
        );
    }
}
