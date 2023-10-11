<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class TakeoverConsequence extends AbstractFlightConsequence
{
    private ShipTakeoverManagerInterface $shipTakeoverManager;

    public function __construct(ShipTakeoverManagerInterface $shipTakeoverManager)
    {
        $this->shipTakeoverManager = $shipTakeoverManager;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();
        $takeover = $ship->getTakeover();

        if ($takeover !== null) {
            $this->shipTakeoverManager->cancelTakeover(
                $ship,
                sprintf(
                    'Die Übernahme der %s wurde abgebrochen, da das Schiff bewegt wurde',
                    $ship->getName()
                )
            );
        }
    }
}
