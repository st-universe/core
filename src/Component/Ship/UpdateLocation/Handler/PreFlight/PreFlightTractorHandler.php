<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation\Handler\PreFlight;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Component\Ship\UpdateLocation\Handler\AbstractUpdateLocationHandler;
use Stu\Component\Ship\UpdateLocation\Handler\UpdateLocationHandlerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class PreFlightTractorHandler extends AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->shipSystemManager = $shipSystemManager;
    }

    public function handle(ShipWrapperInterface $wrapper, ?ShipInterface $tractoringShip): void
    {
        $ship = $wrapper->get();

        $tractoredShip = $ship->getTractoredShip();
        if ($tractoredShip === null) {
            return;
        }

        $tractoredShipFleet = $tractoredShip->getFleet();

        // fleet ships can not be towed
        if (
            $tractoredShipFleet !== null
            && $tractoredShipFleet->getShipCount() > 1
        ) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);  //active deactivation

            $this->addMessageInternal(
                sprintf(
                    _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert'),
                    $tractoredShip->getName()
                )
            );

            return;
        }

        //can tow tractored ship?
        $abortionMsg = $this->tractorMassPayloadUtil->tryToTow($wrapper, $tractoredShip);

        if ($abortionMsg === null) {
            //Traktorstrahl Kosten
            if ($wrapper->getEpsSystemData()->getEps() < $tractoredShip->getRump()->getFlightEcost() + 1) {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
                $this->addMessageInternal(sprintf(
                    _('Der Traktorstrahl auf die %s wurde in Sektor %d|%d aufgrund Energiemangels deaktiviert'),
                    $tractoredShip->getName(),
                    $ship->getPosX(),
                    $ship->getPosY()
                ));
            }
        } else {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
            $this->addMessageInternal($abortionMsg);
        }
    }
}
