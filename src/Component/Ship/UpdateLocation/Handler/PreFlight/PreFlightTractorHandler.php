<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation\Handler\PreFlight;

use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Component\Ship\UpdateLocation\Handler\AbstractUpdateLocationHandler;
use Stu\Component\Ship\UpdateLocation\Handler\UpdateLocationHandlerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;

final class PreFlightTractorHandler extends AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    private TractorMassPayloadUtilInterface $tractorMassPayloadUtil;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        TractorMassPayloadUtilInterface $tractorMassPayloadUtil,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->tractorMassPayloadUtil = $tractorMassPayloadUtil;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(ShipInterface $ship, ?ShipInterface $tractoringShip): void
    {
        if (!$ship->isTractoring()) {
            return;
        }

        $tractoredShip = $ship->getTractoredShip();

        // fleet ships can not be towed
        if (
            $tractoredShip->getFleetId()
            && $tractoredShip->getFleet()->getShipCount() > 1
        ) {
            $this->shipWrapperFactory->wrapShip($ship)->deactivateTractorBeam(); //active deactivation

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
            if ($ship->getEps() < $tractoredShip->getRump()->getFlightEcost() + 1) {
                $this->shipWrapperFactory->wrapShip($ship)->deactivateTractorBeam();
                $this->addMessageInternal(sprintf(
                    _('Der Traktorstrahl auf die %s wurde in Sektor %d|%d aufgrund Energiemangels deaktiviert'),
                    $tractoredShip->getName(),
                    $ship->getPosX(),
                    $ship->getPosY()
                ));
            }
        } else {
            $this->shipWrapperFactory->wrapShip($ship)->deactivateTractorBeam();
            $this->addMessageInternal($abortionMsg);
        }
    }
}
