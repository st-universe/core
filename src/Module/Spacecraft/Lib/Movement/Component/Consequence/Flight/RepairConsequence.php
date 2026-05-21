<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Spacecraft\Repair\CancelRepairResult;
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

    #[\Override]
    protected function skipWhenTractored(): bool
    {
        return false;
    }

    #[\Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if ($ship->getCondition()->isUnderRepair()) {
            $message = $this->messageFactory->createMessage(null, $ship->getUser()->getId());
            $messages->add($message);
            $cancelRepairResult = $this->cancelRepair->cancelRepairWithResult($ship);
            $message->add(sprintf(
                'Die Reparatur der %s wurde abgebrochen%s',
                $ship->getName(),
                $this->getRefundMessageSuffix($cancelRepairResult)
            ));
        }
    }

    private function getRefundMessageSuffix(CancelRepairResult $cancelRepairResult): string
    {
        $refundTexts = [];
        $refundedSpareParts = $cancelRepairResult->getRefundedSpareParts();
        $refundedSystemComponents = $cancelRepairResult->getRefundedSystemComponents();

        if ($refundedSpareParts > 0) {
            $refundTexts[] = sprintf(
                '%d %s',
                $refundedSpareParts,
                $refundedSpareParts === 1 ? 'Ersatzteil' : 'Ersatzteile'
            );
        }

        if ($refundedSystemComponents > 0) {
            $refundTexts[] = sprintf(
                '%d %s',
                $refundedSystemComponents,
                $refundedSystemComponents === 1 ? 'Systemkomponente' : 'Systemkomponenten'
            );
        }

        return match (count($refundTexts)) {
            1 => sprintf(
                '. %s %s zurückerstattet',
                $refundTexts[0],
                $refundedSpareParts + $refundedSystemComponents === 1 ? 'wurde' : 'wurden'
            ),
            2 => sprintf('. %s und %s wurden zurückerstattet', $refundTexts[0], $refundTexts[1]),
            default => ''
        };
    }
}
