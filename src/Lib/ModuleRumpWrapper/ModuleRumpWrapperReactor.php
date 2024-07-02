<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use RuntimeException;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperReactor extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    public function getValue(ModuleInterface $module = null): int
    {
        $module = $module ?? current($this->getModule());
        if ($module === false) {
            return 0;
        }

        return (new ModuleValueCalculator())->calculateModuleValue(
            $this->rump,
            $module,
            $this->rump->getBaseReactor()
        );
    }

    public function getModuleType(): ShipModuleTypeEnum
    {
        return ShipModuleTypeEnum::REACTOR;
    }

    public function apply(ShipWrapperInterface $wrapper): void
    {
        $reactorWrapper = $wrapper->getReactorWrapper();
        if ($reactorWrapper === null) {
            throw new RuntimeException('this should not happen');
        }

        $reactorWrapper->setOutput($this->getValue());
    }
}
