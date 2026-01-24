<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Spacecraft;

class DeactivateShieldsBehaviour implements PirateBehaviourInterface
{
    public function __construct(private SpacecraftSystemManagerInterface $spacecraftSystemManager) {}
    #[\Override]
    public function action(
        PirateFleetBattleParty $pirateFleetBattleParty,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        foreach ($pirateFleetBattleParty->getActiveMembers() as $wrapper) {
            $ship = $wrapper->get();
            if ($ship->getCondition()->getShield() === $ship->getMaxShield()) {
                continue;
            }

            if ($ship->getStorage()->isEmpty() && $ship->isShielded()) {
                $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SHIELDS, true);
            }
        }

        return null;
    }
}
