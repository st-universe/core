<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use RuntimeException;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

final class ModuleRumpWrapperWarpDrive extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
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
            $this->rump->getBaseWarpDrive()
        );
    }

    public function getModuleType(): ShipModuleTypeEnum
    {
        return ShipModuleTypeEnum::WARPDRIVE;
    }

    public function apply(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();

        $systemData = $wrapper->getWarpDriveSystemData();
        if ($systemData === null) {
            throw new RuntimeException('this should not happen');
        }

        $systemData
            ->setMaxWarpDrive($this->getValue())
            ->setWarpDriveSplit(100)
            ->setAutoCarryOver($ship->getUser()->getWarpsplitAutoCarryoverDefault())
            ->update();
    }
}
