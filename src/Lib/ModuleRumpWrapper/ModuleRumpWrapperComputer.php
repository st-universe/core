<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperComputer extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            $this->rump->getHitChance()
        );
    }

    #[Override]
    public function getSecondValue(?ModuleInterface $module = null): ?int
    {
        return null;
    }

    #[Override]
    public function getModuleType(): ShipModuleTypeEnum
    {
        return ShipModuleTypeEnum::COMPUTER;
    }

    #[Override]
    public function apply(ShipWrapperInterface $wrapper): void
    {
        $wrapper->get()->setHitChance($this->getValue());
    }
}
