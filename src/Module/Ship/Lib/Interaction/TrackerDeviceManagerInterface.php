<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface TrackerDeviceManagerInterface
{
    public function deactivateTrackerIfExisting(ShipWrapperInterface $wrapper): void;

    public function resetTrackersOfTrackedShip(
        ShipWrapperInterface $trackedShipWrapper,
        ShipSystemManagerInterface $shipSystemManager
    ): void;
}
