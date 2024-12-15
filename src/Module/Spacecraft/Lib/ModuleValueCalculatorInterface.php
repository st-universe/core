<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;

interface ModuleValueCalculatorInterface
{
    public function calculateModuleValue(
        SpacecraftRumpInterface $rump,
        ModuleInterface $module,
        int $value
    ): int;

    public function calculateDamageImpact(SpacecraftRumpInterface $rump, ModuleInterface $module): string;

    public function calculateEvadeChance(SpacecraftRumpInterface $rump, ModuleInterface $module): int;
}
