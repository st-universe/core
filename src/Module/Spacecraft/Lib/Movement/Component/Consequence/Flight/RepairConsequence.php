<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Override;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class RepairConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    public function __construct(
        private CancelRepairInterface $cancelRepair,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    protected function skipWhenTractored(): bool
    {
        return false;
    }

    #[Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if ($ship->isUnderRepair()) {
            $message = $this->messageFactory->createMessage(null, $ship->getUser()->getId());
            $messages->add($message);
            $this->cancelRepair->cancelRepair($ship);
            $message->add(sprintf(_('Die Reparatur der %s wurde abgebrochen'), $ship->getName()));
        }
    }
}
