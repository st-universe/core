<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperImpulseDrive extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    public function getValue(ModuleInterface $module = null): int
    {
        $moduleValueCalculator = new ModuleValueCalculator();

        $module = $module ?? current($this->getModule());
        if ($module === false) {
            return 0;
        }

        return $moduleValueCalculator->calculateEvadeChance(
            $this->rump,
            $module
        );
    }

    public function getModuleType(): ShipModuleTypeEnum
    {
        return ShipModuleTypeEnum::IMPULSEDRIVE;
    }

    public function apply(ShipWrapperInterface $wrapper): void
    {
        $wrapper->get()->setEvadeChance($this->getValue());
    }
}
