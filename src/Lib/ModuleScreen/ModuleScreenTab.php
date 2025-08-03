<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Orm\Entity\Module;

class ModuleScreenTab
{
    private const string UNSELECTED_CLASS = ' module_selector_unselected';

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
                $class .= self::UNSELECTED_CLASS;
            }
        }

        if ($this->moduleSelector->isMandatory()) {

            $buildplan = $this->moduleSelector->getBuildplan();

            if (!$this->moduleSelector->hasSelectedModule()) {
                $class .= self::UNSELECTED_CLASS;
            } elseif ($buildplan !== null) {

                /** @var Module $mod */
                $mod = $buildplan->getModulesByType($this->moduleSelector->getModuleType())->first();
                $commodityId = $mod->getCommodityId();

                $stor = $this->moduleSelector->getHost()->getStorage()[$commodityId] ?? null;
                if ($stor === null) {
                    $class .= self::UNSELECTED_CLASS;
                }
            }
        }
        return $class;
    }
}
