<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\CancelColonyBlockOrDefendInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class TractorConsequence extends AbstractFlightConsequence
{
    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private ShipSystemManagerInterface $shipSystemManager;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    public function __construct(
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        ShipSystemManagerInterface $shipSystemManager,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend
    ) {
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->shipSystemManager = $shipSystemManager;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        $tractoredShip = $ship->getTractoredShip();
        if ($tractoredShip === null) {
            return;
        }

        $message = new Message();
        $messages->add($message);

        $tractoredShipFleet = $tractoredShip->getFleet();

        if (
            $tractoredShipFleet !== null
            && $tractoredShipFleet->getShipCount() > 1
        ) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
            $message->add(
                sprintf(
                    'Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert',
                    $tractoredShip->getName()
                )
            );

            return;
        }

        //can tow tractored ship?
        $abortionMsg = $this->tractorMassPayloadUtil->tryToTow($wrapper, $tractoredShip);
        if ($abortionMsg !== null) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
            $message->add($abortionMsg);

            return;
        }

        // cancel colony block or defend of tractored ship fleet
        $informations = new InformationWrapper();

        $this->cancelColonyBlockOrDefend->work($ship, $informations, true);

        $message->addMessageMerge($informations->getInformations());
    }
}
