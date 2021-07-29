<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRumpInterface;

interface ModuleSelectorInterface
{
    public function allowMultiple(): bool;

    public function getMacro(): string;

    public function render(): string;

    public function getModuleType(): int;

    public function allowEmptySlot(): bool;

    public function getModuleDescription(): string;

    public function getUserId(): int;

    public function getRump(): ShipRumpInterface;

    /**
     * @return ModuleSelectorWrapper[]
     */
    public function getAvailableModules(): array;

    public function hasModuleSelected(): ModuleSelectWrapper;

    public function getColony(): ?ColonyInterface;

    public function getBuildplan(): ?ShipBuildplanInterface;

    public function getModuleLevelClass(ShipRumpInterface $rump, ModuleSelectorWrapperInterface $module): string;

    public function getModuleValueCalculator(): ModuleValueCalculatorInterface;
}
