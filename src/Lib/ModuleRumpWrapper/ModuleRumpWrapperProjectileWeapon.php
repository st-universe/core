<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Module;

final class ModuleRumpWrapperProjectileWeapon extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            200 //TODO use property of rump / rumpRole
        );
    }

    #[\Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::TORPEDO;
    }

    #[\Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $systemData = $wrapper->getProjectileLauncherSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        $systemData->setShieldPenetration($this->getValue())->update();
    }
}
