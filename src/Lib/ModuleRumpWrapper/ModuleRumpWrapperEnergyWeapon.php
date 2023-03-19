<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperEnergyWeapon extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    public function getValue(): int
    {
        $module = current($this->modules);
        if ($module === false) {
            return 0;
        }

        return (new ModuleValueCalculator())->calculateModuleValue(
            $this->rump,
            $module->getModule(),
            null,
            $this->rump->getBaseDamage()
        );
    }

    public function apply(ShipInterface $ship): void
    {
        $ship->setBaseDamage($this->getValue());
    }
}
