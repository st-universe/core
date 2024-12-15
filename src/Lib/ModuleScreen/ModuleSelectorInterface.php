<?php

namespace Stu\Lib\ModuleScreen;

use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\ModuleScreen\Addon\ModuleSelectorAddonInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface ModuleSelectorInterface
{
    public function isMandatory(): bool;

    public function isSpecial(): bool;

    public function allowMultiple(): bool;

    public function render(): string;

    public function getModuleType(): SpacecraftModuleTypeEnum;

    public function allowEmptySlot(): bool;

    public function getModuleDescription(): string;

    public function getUserId(): int;

    public function getHost(): ColonyInterface|SpacecraftInterface;

    public function getRump(): SpacecraftRumpInterface;

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

    public function getBuildplan(): ?SpacecraftBuildplanInterface;

    public function getAddon(): ?ModuleSelectorAddonInterface;
}
