<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Utility;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Trait\SpacecraftTractorPayloadTrait;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Damage\SystemDamageInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class TractorMassPayloadUtil implements TractorMassPayloadUtilInterface
{
    use SpacecraftTractorPayloadTrait;

    public const float POSSIBLE_DAMAGE_THRESHOLD = 0.9;

    public function __construct(
        private SystemDamageInterface $systemDamage,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private StuRandom $stuRandom,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    public function tryToTow(
        SpacecraftWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        InformationInterface $information
    ): bool {

        $spacecraft = $wrapper->get();
        $ownFleet = $spacecraft instanceof ShipInterface ? $spacecraft->getFleet() : null;
        $tractoredShipFleet = $tractoredShip->getFleet();

        // target in other fleet?
        if (
            $tractoredShipFleet !== null
            && $tractoredShipFleet !== $ownFleet
            && $tractoredShipFleet->getShipCount() > 1
        ) {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true);
            $information->addInformationf(
                'Flottenschiffe können nicht mitgezogen werden - Der auf die %s gerichtete Traktorstrahl wurde deaktiviert',
                $tractoredShip->getName()
            );

            return false;
        }

        $mass = $tractoredShip->getRump()->getTractorMass();
        $payload = $this->getTractorPayload($spacecraft);

        // ship to heavy?
        if ($mass > $payload) {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true);

            $information->addInformationf(
                _('Traktoremitter der %s war nicht leistungsstark genug um die %s zu ziehen und wurde daher deaktiviert'),
                $spacecraft->getName(),
                $tractoredShip->getName()
            );

            return false;
        }

        return true;
    }

    #[Override]
    public function isTractorSystemStressed(
        SpacecraftWrapperInterface $wrapper,
        ShipInterface $tractoredShip
    ): bool {
        $ship = $wrapper->get();
        $mass = $tractoredShip->getRump()->getTractorMass();
        $payload = $this->getTractorPayload($ship);

        // damage tractor system if mass over 90% of max
        return $mass > self::POSSIBLE_DAMAGE_THRESHOLD * $payload;
    }

    #[Override]
    public function stressTractorSystemForTowing(
        SpacecraftWrapperInterface $wrapper,
        ShipInterface $tractoredShip,
        MessageCollectionInterface $messages
    ): void {
        $ship = $wrapper->get();

        if ($this->isTractorSystemStressed($wrapper, $tractoredShip) && $this->stuRandom->rand(1, 10) === 1) {
            $system = $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM);

            $message = $this->messageFactory->createMessage();

            if ($this->systemDamage->damageShipSystem($wrapper, $system, $this->stuRandom->rand(5, 25), $message)) {
                //tractor destroyed
                $message->addInformation(sprintf(
                    _('Traktoremitter der %s wurde zerstört. Die %s wird nicht weiter gezogen'),
                    $ship->getName(),
                    $tractoredShip->getName()
                ));
                $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true);
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
