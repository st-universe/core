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
    public function calculateModuleValue(
        SpacecraftRump $rump,
        Module $module,
        int $value
    ): int {

        $moduleLevel = $rump->getBaseValues()->getModuleLevel();

        if ($module->getType() === SpacecraftModuleTypeEnum::SENSOR) {
            if ($moduleLevel > $module->getLevel()) {
                return (int) round($value -  $module->getDowngradeFactor());
            }
            if ($moduleLevel < $module->getLevel()) {
                return (int) round($value +  $module->getUpgradeFactor());
            }
            if ($moduleLevel === $module->getLevel()) {
                return (int) round($value +  $module->getDefaultFactor());
            }
        } else {
            if ($moduleLevel > $module->getLevel()) {
                return (int) round($value - $value / 100 * $module->getDowngradeFactor());
            }
            if ($moduleLevel < $module->getLevel()) {
                return (int) round($value + $value / 100 * $module->getUpgradeFactor());
            }
            if ($moduleLevel === $module->getLevel()) {
                return (int) round($value + $value / 100 * $module->getDefaultFactor());
            }
        }
        return $value;
    }

    #[Override]
    public function calculateDamageImpact(SpacecraftRump $rump, Module $module): string
    {
        $moduleLevel = $rump->getBaseValues()->getModuleLevel();

        if ($moduleLevel > $module->getLevel()) {
            return '-' . $module->getDowngradeFactor() . '%';
        }
        if ($moduleLevel < $module->getLevel()) {
            return '+' . $module->getUpgradeFactor() . '%';
        }
        if ($moduleLevel === $module->getLevel()) {
            return '+' . $module->getDefaultFactor() . '%';
        }
        return _('Normal');
    }

    #[Override]
    public function calculateEvadeChance(SpacecraftRump $rump, Module $module): int
    {
        $moduleLevel = $rump->getBaseValues()->getModuleLevel();
        $baseEvadeChange = $rump->getBaseValues()->getEvadeChance();

        if ($moduleLevel > $module->getLevel()) {
            $value = (1 - $baseEvadeChange / 100) * 1 / (1 - $module->getDowngradeFactor() / 100);
        } elseif ($moduleLevel < $module->getLevel()) {
            $value = (1 - $baseEvadeChange / 100) * 1 / (1 + $module->getUpgradeFactor() / 100);
        } elseif ($moduleLevel === $module->getLevel()) {
            $value = (1 - $baseEvadeChange / 100) * 1 / (1 + $module->getDefaultFactor() / 100);
        } else {
            return $baseEvadeChange;
        }
        return (int) round((1 - $value) * 100);
    }
}
