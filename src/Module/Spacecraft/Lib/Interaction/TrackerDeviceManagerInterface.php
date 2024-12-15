<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface TrackerDeviceManagerInterface
{
    public function resetTrackersOfTrackedShip(
        SpacecraftWrapperInterface $trackedShipWrapper,
        SpacecraftSystemManagerInterface $spacecraftSystemManager,
        bool $sendPmToTargetOwner
    ): void;

    public function deactivateTrackerIfActive(
        SpacecraftWrapperInterface $wrapper,
        bool $sendPmToTargetOwner
    ): void;
}
