<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Orm\Entity\ModuleInterface;

class ModuleScreenTab
{
    public function __construct(private ModuleSelectorInterface $moduleSelector) {}

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
                /** @var ModuleInterface $mod */
                $mod = $this->moduleSelector->getBuildplan()->getModulesByType($this->moduleSelector->getModuleType())->first();
                $commodityId = $mod->getCommodityId();

                $stor = $this->moduleSelector->getHost()->getStorage()[$commodityId] ?? null;
                if ($stor === null) {
                    $class .= ' module_select_base_mandatory';
                }
            }
        }
        return $class;
    }
}
