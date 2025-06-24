<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperShield extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            $this->rumpBaseValues->getBaseShield()
        );
    }

    #[Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::SHIELDS;
    }

    #[Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        if ($wrapper->get()->getCondition()->getShield() > $this->getValue()) {
            $wrapper->get()->getCondition()->setShield($this->getValue());
        }

        $wrapper->get()->setMaxShield($this->getValue());
    }
}
