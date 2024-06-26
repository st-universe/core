<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

class TrackerDeviceManager implements TrackerDeviceManagerInterface
{
    public function __construct(
        private ShipSystemRepositoryInterface $shipSystemRepository
    ) {
    }

    public function deactivateTrackerIfExisting(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TRACKER)) {
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACKER)->setMode(ShipSystemModeEnum::MODE_OFF);

            $trackerSystemData = $wrapper->getTrackerSystemData();
            if ($trackerSystemData !== null) {
                $trackerSystemData->setTarget(null)->update();
            }
        }
    }

    public function resetTrackersOfTrackedShip(
        ShipWrapperInterface $trackedShipWrapper,
        ShipSystemManagerInterface $shipSystemManager
    ): void {

        $shipWrapperFactory = $trackedShipWrapper->getShipWrapperFactory();

        foreach ($this->shipSystemRepository->getTrackingShipSystems($trackedShipWrapper->get()->getId()) as $system) {
            $wrapper = $shipWrapperFactory->wrapShip($system->getShip());

            $shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACKER, true);
        }
    }
}
