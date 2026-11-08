<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Ship;

final class BorgSphereEnergy
{
    public function __construct(private SpacecraftWrapperFactoryInterface $wrappers) {}

    public function recharge(Ship $ship): void
    {
        $wrapper = $this->wrappers->wrapShip($ship);
        $eps = $wrapper->getEpsSystemData();
        if ($eps !== null) {
            $eps->setEps($eps->getMaxEps())->setBattery($eps->getMaxBattery())->update();
        }
        $warp = $wrapper->getWarpDriveSystemData();
        if ($warp !== null) {
            $warp->setWarpDrive($warp->getMaxWarpdrive())->update();
        }
        $reactor = $wrapper->getReactorWrapper();
        if ($reactor !== null) {
            $reactor->setLoad($reactor->getCapacity())->get()->update();
        }
    }
}
