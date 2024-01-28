<?php

namespace Stu\Module\Tick\Ship\ManagerComponent;

use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class NpcShipHandling implements ManagerComponentInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function work(): void
    {
        // @todo
        foreach ($this->shipRepository->getNpcShipsForTick() as $ship) {
            $wrapper = $this->shipWrapperFactory->wrapShip($ship);
            $reactor = $wrapper->getReactorWrapper();
            if ($reactor === null) {
                continue;
            }

            $epsSystem = $wrapper->getEpsSystemData();
            $warpdrive = $wrapper->getWarpDriveSystemData();

            //load EPS
            if ($epsSystem !== null) {
                $epsSystem->setEps($epsSystem->getEps() + $reactor->getEffectiveEpsProduction())->update();
            }

            //load warpdrive
            if ($warpdrive !== null) {
                $warpdrive->setWarpDrive($warpdrive->getWarpDrive() + $reactor->getEffectiveWarpDriveProduction())->update();
            }
        }
    }
}
