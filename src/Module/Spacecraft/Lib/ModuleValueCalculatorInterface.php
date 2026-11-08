<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftRump;

interface ModuleValueCalculatorInterface
{
    public function calculateModuleValue(
        SpacecraftRump $rump,
        Module $module,
        int $value,
        int $improvementSteps = 0
    ): int;

    public function calculateDamageImpact(SpacecraftRump $rump, Module $module, int $improvementSteps = 0): string;

    public function calculateEvadeChance(SpacecraftRump $rump, Module $module, int $improvementSteps = 0): int;
}
