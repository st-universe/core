<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Orm\Entity\Module;

class ModuleScreenTab
{
    public function __construct(private ModuleSelectorInterface $moduleSelector) {}

    public function getTabTitle(): string
    {
        return $this->moduleSelector->getModuleDescription();
    }

    public function getCssClass(): string
    {
        $class = 'module_selector';

        if ($this->moduleSelector->getAvailableModules() === []) {
            return $class;
        }

        if ($this->moduleSelector->allowEmptySlot()) {

            if ($this->moduleSelector->isEmptySlot()) {
                $class .= ' module_selector_skipped';
            } elseif (!$this->moduleSelector->hasSelectedModule()) {
                $class .= ' module_selector_unselected';
            }
        }

        if ($this->moduleSelector->isMandatory()) {
            if (!$this->moduleSelector->hasSelectedModule()) {
                $class .= ' module_selector_unselected';
            } else {
                /** @var Module $mod */
                $mod = $this->moduleSelector->getBuildplan()->getModulesByType($this->moduleSelector->getModuleType())->first();
                $commodityId = $mod->getCommodityId();

                $stor = $this->moduleSelector->getHost()->getStorage()[$commodityId] ?? null;
                if ($stor === null) {
                    $class .= ' module_selector_unselected';
                }
            }
        }
        return $class;
    }
}
