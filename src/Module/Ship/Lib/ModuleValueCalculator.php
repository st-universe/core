<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;

final class ModuleValueCalculator implements ModuleValueCalculatorInterface
{
    public function calculateModuleValue(
        $rump,
        ModuleInterface $module,
        $callback = 'aggi',
        $value = false
    ): int {
        if (!$value) {
            $value = $rump->$callback();
        }
        if ($module->getType() === ShipModuleTypeEnum::MODULE_TYPE_SENSOR) {
            if ($rump->getModuleLevel() > $module->getLevel()) {
                return (int) round($value -  $module->getDowngradeFactor());
            }
            if ($rump->getModuleLevel() < $module->getLevel()) {
                return (int) round($value +  $module->getUpgradeFactor());
            }
            if ($rump->getModuleLevel() === $module->getLevel()) {
                return (int) round($value +  $module->getDefaultFactor());
            }
        } else {
            if ($rump->getModuleLevel() > $module->getLevel()) {
                return (int) round($value - $value / 100 * $module->getDowngradeFactor());
            }
            if ($rump->getModuleLevel() < $module->getLevel()) {
                return (int) round($value + $value / 100 * $module->getUpgradeFactor());
            }
            if ($rump->getModuleLevel() === $module->getLevel()) {
                return (int) round($value + $value / 100 * $module->getDefaultFactor());
            }
        }
        return (int) $value;
    }

    public function calculateDamageImpact(ShipRumpInterface $rump, ModuleInterface $module): string
    {
        if ($rump->getModuleLevel() > $module->getLevel()) {
            return '-' . $module->getDowngradeFactor() . '%';
        }
        if ($rump->getModuleLevel() < $module->getLevel()) {
            return '+' . $module->getUpgradeFactor() . '%';
        }
        if ($rump->getModuleLevel() === $module->getLevel()) {
            return '+' . $module->getDefaultFactor() . '%';
        }
        return _('Normal');
    }

    public function calculateEvadeChance(ShipRumpInterface $rump, ModuleInterface $module): int
    {
        $base = $rump->getEvadeChance();
        if ($rump->getModuleLevel() > $module->getLevel()) {
            $value = (1 - $base / 100) * 1 / (1 - $module->getDowngradeFactor() / 100);
        } elseif ($rump->getModuleLevel() < $module->getLevel()) {
            $value = (1 - $base / 100) * 1 / (1 + $module->getUpgradeFactor() / 100);
        } elseif ($rump->getModuleLevel() === $module->getLevel()) {
            $value = (1 - $base / 100) * 1 / (1 + $module->getDefaultFactor() / 100);
        } else {
            return $base;
        }
        return (int) round((1 - $value) * 100);
    }
}
