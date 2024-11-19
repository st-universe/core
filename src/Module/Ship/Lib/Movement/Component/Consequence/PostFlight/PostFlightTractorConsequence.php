<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Override;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class PostFlightTractorConsequence extends AbstractFlightConsequence implements PostFlightConsequenceInterface
{
    public function __construct(private TractorMassPayloadUtilInterface $tractorMassPayloadUtil) {}

    #[Override]
    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
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
