<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Override;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class TractorConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    public function __construct(
        private TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    protected function skipWhenTractored(): bool
    {
        return true;
    }

    #[Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        $tractoredShip = $ship->getTractoredShip();
        if ($tractoredShip === null) {
            return;
        }

        $message = $this->messageFactory->createMessage();
        $messages->add($message);

        //can tow tractored ship?
        $canTowTractoredShip = $this->tractorMassPayloadUtil->tryToTow($wrapper, $tractoredShip, $message);
        if ($canTowTractoredShip) {
            $this->cancelColonyBlockOrDefend->work($ship, $message, true);
        }
    }
}
