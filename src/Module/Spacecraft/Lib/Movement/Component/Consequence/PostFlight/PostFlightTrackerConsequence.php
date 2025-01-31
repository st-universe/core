<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class PostFlightTrackerConsequence extends AbstractFlightConsequence implements PostFlightConsequenceInterface
{
    public function __construct(
        private TrackerDeviceManagerInterface $trackerDeviceManager,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    #[Override]
    protected function skipWhenTractored(): bool
    {
        return false;
    }

    #[Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        if ($flightRoute->getRouteMode() !== RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY) {
            return;
        }

        $this->trackerDeviceManager->deactivateTrackerIfActive($wrapper, false);
        $this->trackerDeviceManager->resetTrackersOfTrackedShip(
            $wrapper,
            $this->spacecraftSystemManager,
            false
        );
    }
}
