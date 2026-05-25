<?php

namespace Stu\Lib\ModuleScreen;

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

        $hasSelectedModule = $this->moduleSelector->hasSelectedModule();

        if ($this->moduleSelector->allowEmptySlot()) {

            if ($this->moduleSelector->isEmptySlot()) {
                $class .= ' module_selector_skipped';
            } elseif (!$hasSelectedModule) {
                $class .= self::UNSELECTED_CLASS;
            }
        }

        if ($this->moduleSelector->isMandatory()) {

            if (!$hasSelectedModule) {
                $class .= self::UNSELECTED_CLASS;
            }
        }

        if (
            $hasSelectedModule
            && !str_contains($class, self::UNSELECTED_CLASS)
            && $this->hasUnavailableSelectedModule()
        ) {
            $class .= self::UNSELECTED_CLASS;
        }

        return $class;
    }

    private function hasUnavailableSelectedModule(): bool
    {
        foreach ($this->moduleSelector->getSelectedModules() as $entry) {
            if ($entry->isDisabled()) {
                return true;
            }
        }

        return false;
    }
}
