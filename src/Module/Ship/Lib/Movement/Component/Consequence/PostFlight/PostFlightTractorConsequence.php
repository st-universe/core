<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class PostFlightTractorConsequence extends AbstractFlightConsequence
{
    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    public function __construct(
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil
    ) {
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        FightMessageCollectionInterface $messages
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
