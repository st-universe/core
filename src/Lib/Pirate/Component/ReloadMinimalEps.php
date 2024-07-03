<?php

namespace Stu\Lib\Pirate\Component;

use Override;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class ReloadMinimalEps implements ReloadMinimalEpsInterface
{
    #[Override]
    public function reload(FleetWrapperInterface $fleetWrapper, int $minimalPercentage = 20): void
    {
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $this->reloadEps($wrapper, $minimalPercentage);
            $this->reloadWarpdrive($wrapper, $minimalPercentage);
        }
    }

    private function reloadEps(ShipWrapperInterface $wrapper, int $minimalPercentage): void
    {
        $epsSystem = $wrapper->getEpsSystemData();

        if (
            $epsSystem !== null
            && $epsSystem->getEpsPercentage() < $minimalPercentage
        ) {
            $epsSystem->setEps((int)($epsSystem->getMaxEps() * $minimalPercentage / 100))->update();
        }
    }

    private function reloadWarpdrive(ShipWrapperInterface $wrapper, int $minimalPercentage): void
    {
        $warpdrive = $wrapper->getWarpDriveSystemData();

        if (
            $warpdrive !== null
            && $warpdrive->getWarpdrivePercentage() < $minimalPercentage
        ) {
            $warpdrive->setWarpDrive((int)($warpdrive->getMaxWarpDrive() * $minimalPercentage / 100))->update();
        }
    }
}
