<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Orm\Entity\BuildplanModuleInterface;

class ModuleScreenTab
{
    public function __construct(private ModuleSelectorInterface $moduleSelector)
    {
    }

    public function getTabTitle(): string
    {
        return $this->moduleSelector->getModuleDescription();
    }

    public function getCssClass(): string
    {
        $class = 'module_select_base';
        if ($this->moduleSelector->isMandatory()) {
            if (!$this->moduleSelector->hasSelectedModule()) {
                $class .= ' module_select_base_mandatory';
            } else {
                /** @var BuildplanModuleInterface $mod */
                $mod = current($this->moduleSelector->getBuildplan()->getModulesByType($this->moduleSelector->getModuleType()));
                $commodityId = $mod->getModule()->getCommodityId();

                $stor = $this->moduleSelector->getHost()->getStorage()[$commodityId] ?? null;
                if ($stor === null) {
                    $class .= ' module_select_base_mandatory';
                }
            }
        }
        return $class;
    }
}
