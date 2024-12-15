<?php

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

class NpcShipHandling implements ManagerComponentInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function work(): void
    {
        // @todo
        foreach ($this->spacecraftRepository->getNpcSpacecraftsForTick() as $spacecraft) {
            $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
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
