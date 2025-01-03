<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperSensor extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    #[Override]
    public function getValue(?ModuleInterface $module = null): int
    {
        $module ??= current($this->getModule());
        if ($module === false) {
            return 0;
        }

        return (new ModuleValueCalculator())->calculateModuleValue(
            $this->rump,
            $module,
            $this->rump->getBaseSensorRange()
        );
    }

    #[Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::SENSOR;
    }

    #[Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $wrapper->get()->setSensorRange($this->getValue());
    }
}
