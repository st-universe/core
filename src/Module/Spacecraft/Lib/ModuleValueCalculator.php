<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftRump;

final class ModuleValueCalculator implements ModuleValueCalculatorInterface
{
    #[Override]
    public function calculateModuleValue(SpacecraftRump $rump, Module $module, int $value, int $improvementSteps = 0): int
    {
        $factor = $this->getFactor($rump, $module);
        $result = $module->getType() === SpacecraftModuleTypeEnum::SENSOR
            ? $value + $factor + $value * $improvementSteps / 1000
            : $value + $value * ($factor * 10 + $improvementSteps) / 1000;

        return (int) round($result, 0, PHP_ROUND_HALF_UP);
    }

    #[Override]
    public function calculateDamageImpact(SpacecraftRump $rump, Module $module, int $improvementSteps = 0): string
    {
        $factor = $this->getFactor($rump, $module) + $improvementSteps / 10;
        return ($factor < 0 ? '' : '+') . rtrim(rtrim(number_format($factor, 1, '.', ''), '0'), '.') . '%';
    }

    #[Override]
    public function calculateEvadeChance(SpacecraftRump $rump, Module $module, int $improvementSteps = 0): int
    {
        $factor = $this->getFactor($rump, $module) + $improvementSteps / 10;
        $baseEvadeChance = $rump->getBaseValues()->getEvadeChance();
        $value = (1 - $baseEvadeChance / 100) / max(0.01, 1 + $factor / 100);

        return (int) round((1 - $value) * 100, 0, PHP_ROUND_HALF_UP);
    }

    private function getFactor(SpacecraftRump $rump, Module $module): float
    {
        $moduleLevel = $rump->getBaseValues()->getModuleLevel();
        return match (true) {
            $moduleLevel > $module->getLevel() => -$module->getDowngradeFactor(),
            $moduleLevel < $module->getLevel() => $module->getUpgradeFactor(),
            default => $module->getDefaultFactor(),
        };
    }
}
