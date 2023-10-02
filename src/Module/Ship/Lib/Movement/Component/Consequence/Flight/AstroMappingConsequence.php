<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessage;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
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
        FightMessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $message = new FightMessage(
                null,
                $ship->getUser()->getId(),
                [sprintf('Die %s hat die Kartographierung abgebrochen', $ship->getName())]
            );
            $messages->add($message);

            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }
    }
}
