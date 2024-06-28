<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class AstroMappingConsequence extends AbstractFlightConsequence
{
    private AstroEntryLibInterface $astroEntryLib;

    public function __construct(AstroEntryLibInterface $astroEntryLib)
    {
        $this->astroEntryLib = $astroEntryLib;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $message = new Message(
                null,
                $ship->getUser()->getId(),
                [sprintf('Die %s hat die Kartographierung abgebrochen', $ship->getName())]
            );
            $messages->add($message);

            $this->astroEntryLib->cancelAstroFinalizing($wrapper);
        }
    }
}
