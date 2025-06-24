<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperEps extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            $this->rumpBaseValues->getBaseEps()
        );
    }

    #[Override]
    public function getSecondValue(?ModuleInterface $module = null): int
    {
        $module ??= current($this->getModule());
        if ($module === false) {
            return 0;
        }
        return (int) round($this->getValue($module) / 3);
    }

    #[Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::EPS;
    }

    #[Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($epsSystem->getEps() > $this->getValue()) {
            $epsSystem->setEps($this->getValue());
        }

        $epsSystem->setMaxEps($this->getValue())->update();
    }
}
