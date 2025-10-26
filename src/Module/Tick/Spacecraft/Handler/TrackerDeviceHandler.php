<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use RuntimeException;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class TrackerDeviceHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private TrackerDeviceManagerInterface $trackerDeviceManager
    ) {}

    #[\Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {
        if (!$wrapper instanceof ShipWrapperInterface) {
            return;
        }

        $ship = $wrapper->get();
        $tracker = $wrapper->getTrackerSystemData();

        if ($tracker === null || $tracker->targetId === null) {
            return;
        }

        $targetWrapper = $tracker->getTargetWrapper();
        if ($targetWrapper === null) {
            throw new RuntimeException('should not happen');
        }

        $target = $targetWrapper->get();
        $shipLocation = $ship->getLocation();
        $targetLocation = $target->getLocation();
        $remainingTicks = $tracker->getRemainingTicks();

        $reduceByTicks = max(1, (int)ceil((abs($shipLocation->getCx() - $targetLocation->getCx())
            +  abs($shipLocation->getCy() - $targetLocation->getCy())) / 50));

        //reduce remaining ticks
        if ($remainingTicks > $reduceByTicks) {
            $tracker->setRemainingTicks($remainingTicks - $reduceByTicks)->update();
        } else {
            $this->trackerDeviceManager->deactivateTrackerIfActive($wrapper, true);
        }
    }
}
