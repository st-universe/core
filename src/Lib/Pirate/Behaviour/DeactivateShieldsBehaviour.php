<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

class DeactivateShieldsBehaviour implements PirateBehaviourInterface
{
    public function __construct(private SpacecraftSystemManagerInterface $spacecraftSystemManager) {}
    #[\Override]
    public function action(
        FleetWrapperInterface $fleetWrapper,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $ship = $wrapper->get();
            if ($ship->getCondition()->getShield() === $ship->getMaxShield()) {
                continue;
            }

            if ($ship->getStorage()->isEmpty()) {
                $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SHIELDS, true);
            }
        }

        return null;
    }
}
