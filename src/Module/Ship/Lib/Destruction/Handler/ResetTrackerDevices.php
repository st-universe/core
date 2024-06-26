<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class ResetTrackerDevices implements ShipDestructionHandlerInterface
{
    public function __construct(
        private TrackerDeviceManagerInterface $trackerDeviceManager,
        private ShipSystemManagerInterface $shipSystemManager
    ) {
    }

    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $this->trackerDeviceManager->resetTrackersOfTrackedShip(
            $destroyedShipWrapper,
            $this->shipSystemManager
        );
    }
}
