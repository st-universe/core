<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class ResetTrackerDevices implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private TrackerDeviceManagerInterface $trackerDeviceManager,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        if ($destroyedSpacecraftWrapper instanceof ShipWrapperInterface) {
            $this->trackerDeviceManager->resetTrackersOfTrackedShip(
                $destroyedSpacecraftWrapper,
                $this->spacecraftSystemManager,
                false
            );
        }
    }
}
