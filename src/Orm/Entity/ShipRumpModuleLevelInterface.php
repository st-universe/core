<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;

interface ShipRumpModuleLevelInterface
{
    public const int MODULE_MANDATORY = 1;

    public function getMinimumLevel(SpacecraftModuleTypeEnum $type): int;

    public function getDefaultLevel(SpacecraftModuleTypeEnum $type): int;

    public function getMaximumLevel(SpacecraftModuleTypeEnum $type): int;

    public function isMandatory(SpacecraftModuleTypeEnum $type): bool;

    /** @param int|bool|string $value */
    public function setValue(SpacecraftModuleTypeEnum $type, string $key, $value): ShipRumpModuleLevelInterface;

    public function getMandatoryModulesCount(): ?int;
}
