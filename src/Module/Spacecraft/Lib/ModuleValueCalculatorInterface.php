<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftRump;

interface ModuleValueCalculatorInterface
{
    public function calculateModuleValue(
        SpacecraftRump $rump,
        Module $module,
        int $value
    ): int;

    public function calculateDamageImpact(SpacecraftRump $rump, Module $module): string;

    public function calculateEvadeChance(SpacecraftRump $rump, Module $module): int;
}
