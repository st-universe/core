<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;

interface ModuleSelectorInterface
{
    public function isMandatory(): bool;

    public function isSpecial(): bool;

    public function allowMultiple(): bool;

    public function render(): string;

    public function getModuleType(): ShipModuleTypeEnum;

    public function allowEmptySlot(): bool;

    public function getModuleDescription(): string;

    public function getUserId(): int;

    public function getHost(): ColonyInterface|ShipInterface;

    public function getRump(): ShipRumpInterface;

    /**
     * @return ModuleSelectorEntryInterface[]
     */
    public function getAvailableModules(): array;

    public function hasSelectedModule(): bool;

    public function getSelectedModuleCount(): int;

    /**
     * @return array<int, ModuleSelectorEntryInterface>
     */
    public function getSelectedModules();

    public function getModuleTypeLevel(): int;

    public function getBuildplan(): ?ShipBuildplanInterface;

    public function getAddon(): ?ModuleSelectorAddonInterface;
}
