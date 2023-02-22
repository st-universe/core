<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipRumpInterface;

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
        if ($rump->getModuleLevel() > $module->getLevel()) {
            return (int) round($value - $value / 100 * $module->getDowngradeFactor());
        }
        if ($rump->getModuleLevel() < $module->getLevel()) {
            return (int) round($value + $value / 100 * $module->getUpgradeFactor());
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
        return _('Normal');
    }

    public function calculateEvadeChance(ShipRumpInterface $rump, ModuleInterface $module): int
    {
        $base = $rump->getEvadeChance();
        if ($rump->getModuleLevel() > $module->getLevel()) {
            $value = (1 - $base / 100) * 1 / (1 - $module->getDowngradeFactor() / 100);
        } elseif ($rump->getModuleLevel() < $module->getLevel()) {
            $value = (1 - $base / 100) * 1 / (1 + $module->getUpgradeFactor() / 100);
        } else {
            return $base;
        }
        return (int) round((1 - $value) * 100);
    }
}
