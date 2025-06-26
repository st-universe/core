<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Module;

final class ModuleRumpWrapperImpulseDrive extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    #[Override]
    public function getValue(?Module $module = null): int
    {
        $moduleValueCalculator = new ModuleValueCalculator();

        $module ??= current($this->getModule());
        if ($module === false) {
            return 0;
        }

        return $moduleValueCalculator->calculateEvadeChance(
            $this->rump,
            $module
        );
    }

    #[Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::IMPULSEDRIVE;
    }

    #[Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $wrapper->getComputerSystemDataMandatory()->setEvadeChance($this->getValue())->update();
    }
}
