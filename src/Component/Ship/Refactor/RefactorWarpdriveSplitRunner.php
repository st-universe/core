<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Refactor;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class RefactorWarpdriveSplitRunner
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

    public function refactor(): void
    {
        foreach ($this->shipRepository->findAll() as $ship) {
            if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPCORE)) {
                continue;
            }

            $wrapper = $this->shipWrapperFactory->wrapShip($ship);

            $warpcore = $wrapper->getWarpCoreSystemData();
            $warpdrive = $wrapper->getWarpDriveSystemData();

            if ($warpcore === null || $warpdrive === null) {
                continue;
            }

            $warpdrive->setWarpCoreSplit($warpcore->getWarpCoreSplit())
                ->update();
        }
    }
}
