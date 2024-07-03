<?php

namespace Stu\Lib\Pirate\Behaviour;

use Override;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class DeactivateShieldsBehaviour implements PirateBehaviourInterface
{
    public function __construct(private ShipSystemManagerInterface $shipSystemManager)
    {
    }
    #[Override]
    public function action(
        FleetWrapperInterface $fleetWrapper,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?ShipInterface $triggerShip
    ): ?PirateBehaviourEnum {

        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $ship = $wrapper->get();
            if ($ship->getShield() === $ship->getMaxShield()) {
                continue;
            }

            if ($ship->getStorage()->isEmpty()) {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS, true);
            }
        }

        return null;
    }
}
