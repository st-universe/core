<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Module;

final class ModuleRumpWrapperEnergyWeapon extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    #[\Override]
    public function getValue(?Module $module = null): int
    {
        $module ??= current($this->getModule());
        if ($module === false) {
            return 0;
        }

        return (new ModuleValueCalculator())->calculateModuleValue(
            $this->rump,
            $module,
            $this->rumpBaseValues->getBaseDamage()
        );
    }

    #[\Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::PHASER;
    }

    #[\Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $energyWeapon = $wrapper->getEnergyWeaponSystemData();
        if ($energyWeapon === null) {
            throw new RuntimeException('this should not happen');
        }
        $energyWeapon->setBaseDamage($this->getValue())->update();
    }
}
