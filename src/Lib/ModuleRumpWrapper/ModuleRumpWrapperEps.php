<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use RuntimeException;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperEps extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            $this->rump->getBaseEps()
        );
    }

    public function getModuleType(): ShipModuleTypeEnum
    {
        return ShipModuleTypeEnum::EPS;
    }

    public function apply(ShipWrapperInterface $wrapper): void
    {
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            throw new RuntimeException('this should not happen');
        }

        $wrapper->getEpsSystemData()->setMaxEps($this->getValue())->update();
    }
}
