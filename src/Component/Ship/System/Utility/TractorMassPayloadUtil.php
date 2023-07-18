<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Utility;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

//TODO unit tests
final class TractorMassPayloadUtil implements TractorMassPayloadUtilInterface
{
    private ApplyDamageInterface $applyDamage;

    private ShipSystemManagerInterface $shipSystemManager;


    public function __construct(
        ApplyDamageInterface $applyDamage,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->applyDamage = $applyDamage;
        $this->shipSystemManager = $shipSystemManager;
    }

    public function tryToTow(ShipWrapperInterface $wrapper, ShipInterface $tractoredShip): ?string
    {
        $ship = $wrapper->get();
        $mass = $tractoredShip->getRump()->getTractorMass();
        $payload = $ship->getTractorPayload();

        // ship to heavy?
        if ($mass > $payload) {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);

            return sprintf(
                _('Traktoremitter der %s war nicht stark genug um die %s zu ziehen und wurde daher deaktiviert'),
                $ship->getName(),
                $tractoredShip->getName()
            );
        }

        return null;
    }

    public function tractorSystemSurvivedTowing(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        InformationWrapper $informations
    ): bool {
        $ship = $wrapper->get();
        $mass = $tractoredShip->getRump()->getTractorMass();
        $payload = $ship->getTractorPayload();

        // damage tractor system if mass over 90% of max
        if (($mass > 0.9 * $payload) && random_int(1, 10) === 1) {
            $system = $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM);

            if ($this->applyDamage->damageShipSystem($wrapper, $system, random_int(5, 25), $informations)) {
                //tractor destroyed
                $informations->addInformation(sprintf(
                    _('Traktoremitter der %s wurde zerstört. Die %s wird nicht weiter gezogen'),
                    $ship->getName(),
                    $tractoredShip->getName()
                ));
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);

                return false;
            } else {
                $informations->addInformation(sprintf(
                    _('Traktoremitter der %s ist überbelastet und wurde dadurch beschädigt, Status: %d%%'),
                    $ship->getName(),
                    $system->getStatus()
                ));
            }
        }

        return true;
    }
}
