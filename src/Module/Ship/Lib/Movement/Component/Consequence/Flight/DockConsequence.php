<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class DockConsequence extends AbstractFlightConsequence
{
    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if ($ship->getDockedTo() !== null) {
            $ship->setDockedTo(null);

            $message = new Message(null, $ship->getUser()->getId());
            $messages->add($message);

            if ($ship->isTractored()) {
                //TODO andockschleuse schrotten, wenn passiv
                $message->add(sprintf('Die %s wurde abgedockt', $ship->getName()));
            } else {
                $message->add(sprintf('Die %s wurde abgedockt', $ship->getName()));
            }
        }
    }
}
