<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class PostFlightTractorConsequence extends AbstractFlightConsequence implements PostFlightConsequenceInterface
{
    public function __construct(private TractorMassPayloadUtilInterface $tractorMassPayloadUtil) {}

    #[\Override]
    protected function skipWhenTractored(): bool
    {
        return true;
    }

    #[\Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $tractoredShip = $wrapper->get()->getTractoredShip();
        if ($tractoredShip === null) {
            return;
        }

        //check for tractor system health
        $this->tractorMassPayloadUtil->stressTractorSystemForTowing(
            $wrapper,
            $tractoredShip,
            $messages
        );
    }
}
