<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperImpulseDrive extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    #[Override]
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

    #[Override]
    public function getModuleType(): ShipModuleTypeEnum
    {
        return ShipModuleTypeEnum::IMPULSEDRIVE;
    }

    #[Override]
    public function apply(ShipWrapperInterface $wrapper): void
    {
        $wrapper->get()->setEvadeChance($this->getValue());
    }
}
