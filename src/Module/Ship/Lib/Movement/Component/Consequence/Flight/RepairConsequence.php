<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Module\Ship\Lib\Battle\Message\Message;
use Stu\Module\Ship\Lib\Battle\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class RepairConsequence extends AbstractFlightConsequence
{
    private CancelRepairInterface $cancelRepair;

    public function __construct(CancelRepairInterface $cancelRepair)
    {
        $this->cancelRepair = $cancelRepair;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if ($ship->isUnderRepair()) {
            $message = new Message(null, $ship->getUser()->getId());
            $messages->add($message);
            $this->cancelRepair->cancelRepair($ship);
            $message->add(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getName()));
        }
    }
}
