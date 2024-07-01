<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use RuntimeException;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperProjectileWeapon extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    public function getValue(ModuleInterface $module = null): int
    {
        $module = $module ?? current($this->getModule());
        if ($module === false) {
            return 0;
        }

        return (new ModuleValueCalculator())->calculateModuleValue(
            $this->rump,
            $module,
            null,
            200 //TODO use property of rump / rumpRole
        );
    }

    public function getModuleType(): ShipModuleTypeEnum
    {
        return ShipModuleTypeEnum::TORPEDO;
    }

    public function apply(ShipWrapperInterface $wrapper): void
    {
        $systemData = $wrapper->getProjectileLauncherSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        $systemData->setShieldPenetration($this->getValue())->update();
    }
}
