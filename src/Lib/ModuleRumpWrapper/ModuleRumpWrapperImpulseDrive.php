<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperImpulseDrive extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    #[Override]
    public function getValue(?ModuleInterface $module = null): int
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
    public function getSecondValue(?ModuleInterface $module = null): ?int
    {
        return null;
    }

    #[Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::IMPULSEDRIVE;
    }

    #[Override]
    public function apply(ShipWrapperInterface $wrapper): void
    {
        $wrapper->get()->setEvadeChance($this->getValue());
    }
}
