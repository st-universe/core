<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Ship\Lib\FleetWrapperInterface;

class ReloadMinimalEps implements ReloadMinimalEpsInterface
{

    public function reload(FleetWrapperInterface $fleetWrapper): void
    {
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $epsSystem = $wrapper->getEpsSystemData();

            if (
                $epsSystem !== null
                && $epsSystem->getEpsPercentage() < 20
            ) {
                $epsSystem->setEps((int)($epsSystem->getMaxEps() * 0.2))->update();
            }
        }
    }
}
