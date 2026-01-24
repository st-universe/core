<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionMetadata;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Spacecraft;

interface PirateBehaviourInterface
{
    /** @return PirateBehaviourEnum alternative behaviour */
    public function action(
        PirateFleetBattleParty $pirateFleetBattleParty,
        PirateReactionInterface $pirateReaction,
        PirateReactionMetadata $reactionMetadata,
        ?Spacecraft $triggerSpacecraft
    ): ?PirateBehaviourEnum;
}
