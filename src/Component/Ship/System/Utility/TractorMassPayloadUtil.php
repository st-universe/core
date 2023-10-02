<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Utility;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Battle\Message\FightMessage;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class TractorMassPayloadUtil implements TractorMassPayloadUtilInterface
{
    public const POSSIBLE_DAMAGE_THRESHOLD = 0.9;

    private ApplyDamageInterface $applyDamage;

    private ShipSystemManagerInterface $shipSystemManager;

    private StuRandom $stuRandom;

    public function __construct(
        ApplyDamageInterface $applyDamage,
        ShipSystemManagerInterface $shipSystemManager,
        StuRandom $stuRandom
    ) {
        $this->applyDamage = $applyDamage;
        $this->shipSystemManager = $shipSystemManager;
        $this->stuRandom = $stuRandom;
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

    public function stressTractorSystemForTowing(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        FightMessageCollectionInterface $messages
    ): void {
        $ship = $wrapper->get();
        $mass = $tractoredShip->getRump()->getTractorMass();
        $payload = $ship->getTractorPayload();

        // damage tractor system if mass over 90% of max
        if (($mass > self::POSSIBLE_DAMAGE_THRESHOLD * $payload) && $this->stuRandom->rand(1, 10) === 1) {
            $system = $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM);

            $informations = new InformationWrapper();

            if ($this->applyDamage->damageShipSystem($wrapper, $system, $this->stuRandom->rand(5, 25), $informations)) {
                //tractor destroyed
                $informations->addInformation(sprintf(
                    _('Traktoremitter der %s wurde zerstört. Die %s wird nicht weiter gezogen'),
                    $ship->getName(),
                    $tractoredShip->getName()
                ));
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
            } else {
                $informations->addInformation(sprintf(
                    _('Traktoremitter der %s ist überbelastet und wurde dadurch beschädigt, Status: %d%%'),
                    $ship->getName(),
                    $system->getStatus()
                ));
            }

            $messages->add(new FightMessage(null, null, $informations->getInformations()));
        }
    }
}
