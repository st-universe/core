<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipRumpInterface;

interface ModuleValueCalculatorInterface
{
    public function calculateModuleValue(
        ShipRumpInterface $rump,
        ModuleInterface $module,
        int $value
    ): int;

    public function calculateDamageImpact(ShipRumpInterface $rump, ModuleInterface $module): string;

    public function calculateEvadeChance(ShipRumpInterface $rump, ModuleInterface $module): int;
}
