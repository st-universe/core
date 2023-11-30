<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;

interface ModuleSelectorWrapperInterface
{
    public function isChosen(): bool;

    public function getBuildplan(): ?ShipBuildplanInterface;

    public function getModule(): ModuleInterface;

    public function getNeededCrew(): int;
}
