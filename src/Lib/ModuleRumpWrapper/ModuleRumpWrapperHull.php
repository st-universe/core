<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperHull extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            $this->rump->getBaseHull()
        );
    }

    #[Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::HULL;
    }

    #[Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $actualHull = $wrapper->get()->getHull();
        $value = $this->getValue();
        if (
            $actualHull === 0
            || $actualHull > $value
        ) {
            $wrapper->get()->setHuell($value);
        }

        $wrapper->get()->setMaxHuell($value);
    }
}
