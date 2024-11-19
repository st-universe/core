<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Override;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Component\Ship\ShipEnum;

class DockConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    public function __construct(
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if ($ship->getDockedTo() !== null) {

            $message = $this->messageFactory->createMessage(null, $ship->getUser()->getId());
            $messages->add($message);
            $epsSystem = $wrapper->getEpsSystemData();
            if ($epsSystem === null || $epsSystem->getEps() < ShipEnum::SYSTEM_ECOST_DOCK) {
                $message->add(sprintf('%s konnte wegen Energiemangels nicht abgedockt werden', $ship->getName()));
                return;
            } else {
                $epsSystem->lowerEps(ShipEnum::SYSTEM_ECOST_DOCK)->update();
                $ship->setDockedTo(null);
            }

            if ($ship->isTractored()) {
                //TODO andockschleuse schrotten, wenn passiv
                $message->add(sprintf('Die %s wurde abgedockt', $ship->getName()));
            } else {
                $message->add(sprintf('Die %s wurde abgedockt', $ship->getName()));
            }
        }
    }
}
