<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperWarpDrive extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            $this->rump->getBaseWarpDrive()
        );
    }

    #[Override]
    public function getModuleType(): SpacecraftModuleTypeEnum
    {
        return SpacecraftModuleTypeEnum::WARPDRIVE;
    }

    #[Override]
    public function initialize(SpacecraftWrapperInterface $wrapper): ModuleRumpWrapperInterface
    {
        $systemData = $wrapper->getWarpDriveSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        $systemData
            ->setAutoCarryOver($wrapper->get()->getUser()->getWarpsplitAutoCarryoverDefault())
            ->update();

        return $this;
    }

    #[Override]
    public function apply(SpacecraftWrapperInterface $wrapper): void
    {
        $systemData = $wrapper->getWarpDriveSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($systemData->getWarpDrive() > $this->getValue()) {
            $systemData->setWarpDrive($this->getValue());
        }

        $systemData
            ->setMaxWarpDrive($this->getValue())
            ->update();
    }
}
