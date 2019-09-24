<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperEnergyWeapon extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{

    public function getValue(): int
    {
        return calculateModuleValue(
            $this->rump,
            current($this->modules)->getModule(),
            null,
            $this->rump->getBaseDamage()
        );
    }

    public function apply(ShipInterface $ship): void
    {
        $ship->setBaseDamage($this->getValue());
    }
}