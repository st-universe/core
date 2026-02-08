<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;

interface ModuleSelectorInterface
{
    public function isMandatory(): bool;

    public function isSpecial(): bool;

    public function allowMultiple(): bool;

    public function render(): string;

    public function getModuleType(): SpacecraftModuleTypeEnum;

    public function allowEmptySlot(): bool;

    public function isEmptySlot(): bool;

    public function getModuleDescription(): string;

    public function getUserId(): int;

    public function getHost(): Colony|Spacecraft;

    public function getRump(): SpacecraftRump;

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

    public function getBuildplan(): ?SpacecraftBuildplan;

    public function getAddon(): ?ModuleSelectorAddonInterface;
}
