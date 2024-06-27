<?php

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface TrackerDeviceManagerInterface
{
    public function resetTrackersOfTrackedShip(
        ShipWrapperInterface $trackedShipWrapper,
        ShipSystemManagerInterface $shipSystemManager,
        bool $sendPmToTargetOwner
    ): void;

    public function deactivateTrackerIfActive(
        ShipWrapperInterface $wrapper,
        bool $sendPmToTargetOwner
    ): void;
}
