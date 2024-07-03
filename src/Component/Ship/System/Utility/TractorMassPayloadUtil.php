<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Utility;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class TractorMassPayloadUtil implements TractorMassPayloadUtilInterface
{
    public const float POSSIBLE_DAMAGE_THRESHOLD = 0.9;

    public function __construct(
        private ApplyDamageInterface $applyDamage,
        private ShipSystemManagerInterface $shipSystemManager,
        private StuRandom $stuRandom,
        private MessageFactoryInterface $messageFactory
    ) {
    }

    #[Override]
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

    #[Override]
    public function isTractorSystemStressed(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip
    ): bool {
        $ship = $wrapper->get();
        $mass = $tractoredShip->getRump()->getTractorMass();
        $payload = $ship->getTractorPayload();

        // damage tractor system if mass over 90% of max
        return $mass > self::POSSIBLE_DAMAGE_THRESHOLD * $payload;
    }

    #[Override]
    public function stressTractorSystemForTowing(
        ShipWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        MessageCollectionInterface $messages
    ): void {
        $ship = $wrapper->get();

        if ($this->isTractorSystemStressed($wrapper, $tractoredShip) && $this->stuRandom->rand(1, 10) === 1) {
            $system = $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM);

            $message = $this->messageFactory->createMessage();

            if ($this->applyDamage->damageShipSystem($wrapper, $system, $this->stuRandom->rand(5, 25), $message)) {
                //tractor destroyed
                $message->addInformation(sprintf(
                    _('Traktoremitter der %s wurde zerstört. Die %s wird nicht weiter gezogen'),
                    $ship->getName(),
                    $tractoredShip->getName()
                ));
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);
            } else {
                $message->addInformation(sprintf(
                    _('Traktoremitter der %s ist überbelastet und wurde dadurch beschädigt, Status: %d%%'),
                    $ship->getName(),
                    $system->getStatus()
                ));
            }

            $messages->add($message);
        }
    }
}
