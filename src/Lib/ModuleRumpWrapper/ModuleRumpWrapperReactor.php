<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

final class ModuleRumpWrapperReactor extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            $this->rumpBaseValues->getBaseReactor()
        );
    }

    #[Override]
    public function getSecondValue(?ModuleInterface $module = null): int
    {
        $module ??= current($this->getModule());
        if ($module === false) {
            return 0;
        }
        if ($module->getSystemType() == SpacecraftSystemTypeEnum::SINGULARITY_REACTOR) {
            return $this->getValue($module) * ReactorWrapperInterface::SINGULARITY_CAPACITY_MULTIPLIER;
        }
        if ($module->getSystemType() == SpacecraftSystemTypeEnum::FUSION_REACTOR) {
            return $this->getValue($module) * ReactorWrapperInterface::FUSIONCORE_CAPACITY_MULTIPLIER;
        } else {
            return $this->getValue($module) * ReactorWrapperInterface::WARPCORE_CAPACITY_MULTIPLIER;
        }
    }

    #[Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::REACTOR;
    }

    #[Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $reactorWrapper = $wrapper->getReactorWrapper();
        if ($reactorWrapper === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($reactorWrapper->getLoad() > $this->getSecondValue() && $this->getSecondValue()) {
            $reactorWrapper->setLoad($this->getSecondValue());
        }

        $reactorWrapper->setOutput($this->getValue());
    }
}
