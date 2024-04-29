<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\FleetWrapperInterface;

class ReloadMinimalEps implements ReloadMinimalEpsInterface
{
    public function reload(FleetWrapperInterface $fleetWrapper, int $minimalPercentage = 20): void
    {
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $epsSystem = $wrapper->getEpsSystemData();

            if (
                $epsSystem !== null
                && $epsSystem->getEpsPercentage() < $minimalPercentage
            ) {
                $epsSystem->setEps((int)($epsSystem->getMaxEps() * $minimalPercentage / 100))->update();
            }
        }
    }
}
