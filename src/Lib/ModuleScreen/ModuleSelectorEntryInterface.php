<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Stu\Orm\Entity\Module;

interface ModuleSelectorEntryInterface
{
    public function isChosen(): bool;

    public function isDisabled(): bool;

    public function getModule(): Module;

    public function getNeededCrew(): int;

    public function getModuleLevelClass(): string;

    public function getStoredAmount(): int;

    public function getValue(): int;

    public function getSecondvalue(): ?int;

    /** @return array<mixed> */
    public function getAddonValues(): array;
}
