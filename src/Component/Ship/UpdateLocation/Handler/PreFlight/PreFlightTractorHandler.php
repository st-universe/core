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
        if (!$ship->isTractoring()) {
            return;
        }

        $tractoredShip = $ship->getTractoredShip();

        // fleet ships can not be towed
        if (
            $tractoredShip->getFleetId()
            && $tractoredShip->getFleet()->getShipCount() > 1
        ) {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);  //active deactivation

            $this->addMessageInternal(
                sprintf(
                    _('Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert'),
                    $ship->getTractoredShip()->getName()
                )
            );

            return;
        }

        //can tow tractored ship?
        $abortionMsg = $this->tractorMassPayloadUtil->tryToTow($ship, $tractoredShip);

        if ($abortionMsg === null) {

            //Traktorstrahl Kosten
            if ($wrapper->getEpsShipSystem()->getEps() < $tractoredShip->getRump()->getFlightEcost() + 1) {
                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
                $this->addMessageInternal(sprintf(
                    _('Der Traktorstrahl auf die %s wurde in Sektor %d|%d aufgrund Energiemangels deaktiviert'),
                    $tractoredShip->getName(),
                    $ship->getPosX(),
                    $ship->getPosY()
                ));
            }
        } else {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
            $this->addMessageInternal($abortionMsg);
        }
    }
}
