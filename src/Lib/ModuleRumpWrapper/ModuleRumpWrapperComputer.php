<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Module;

final class ModuleRumpWrapperComputer extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    #[\Override]
    public function getValue(?Module $module = null): int
    {
        $module ??= current($this->getModule());
        if ($module === false) {
            return 0;
        }

        return new ModuleValueCalculator()->calculateModuleValue(
            $this->rump,
            $module,
            $this->rumpBaseValues->getHitChance()
        );
    }

    #[\Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::COMPUTER;
    }

    #[\Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $wrapper->getComputerSystemDataMandatory()->setHitChance($this->getValue())->update();
    }
}
