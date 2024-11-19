<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Ship\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class PostFlightTrackerConsequence extends AbstractFlightConsequence implements PostFlightConsequenceInterface
{
    public function __construct(
        private TrackerDeviceManagerInterface $trackerDeviceManager,
        private ShipSystemManagerInterface $shipSystemManager
    ) {}

    #[Override]
    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        if ($flightRoute->getRouteMode() !== RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY) {
            return;
        }

        $this->trackerDeviceManager->deactivateTrackerIfActive($wrapper, false);
        $this->trackerDeviceManager->resetTrackersOfTrackedShip(
            $wrapper,
            $this->shipSystemManager,
            false
        );
    }
}
