<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use RuntimeException;
use Stu\Module\Ship\Lib\ModuleValueCalculator;
use Stu\Orm\Entity\ShipInterface;

final class ModuleRumpWrapperWarpDrive extends ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    public function getValue(): int
    {
        $module = current($this->modules);
        if ($module === false) {
            return 0;
        }

        return (new ModuleValueCalculator())->calculateModuleValue(
            $this->rump,
            $module->getModule(),
            null,
            $this->rump->getBaseWarpDrive()
        );
    }

    public function apply(ShipInterface $ship): void
    {
        $systemData = $this->wrapper->getWarpDriveSystemData();
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
