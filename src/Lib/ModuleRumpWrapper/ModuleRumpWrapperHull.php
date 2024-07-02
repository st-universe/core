<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperHull extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            $this->rump->getBaseHull()
        );
    }

    public function getModuleType(): ShipModuleTypeEnum
    {
        return ShipModuleTypeEnum::HULL;
    }

    public function apply(ShipWrapperInterface $wrapper): void
    {
        $value = $this->getValue();
        $wrapper->get()->setMaxHuell($value);
        $wrapper->get()->setHuell($value);
    }
}
