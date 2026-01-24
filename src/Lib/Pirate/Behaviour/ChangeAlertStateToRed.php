<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Spacecraft;

class ChangeAlertStateToRed implements PirateBehaviourInterface
{
    #[\Override]
    public function action(
        PirateFleetBattleParty $pirateFleetBattleParty,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum {

        foreach ($pirateFleetBattleParty->getActiveMembers() as $wrapper) {
            $spacecraft = $wrapper->get();

            if (
                !$spacecraft->getCondition()->isDestroyed()
                && $spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER)
            ) {
                $wrapper->setAlertState(SpacecraftAlertStateEnum::ALERT_RED);
            }
        }

        return null;
    }
}
