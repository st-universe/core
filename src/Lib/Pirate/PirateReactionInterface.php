<?php

namespace Stu\Lib\Pirate;

use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Orm\Entity\Spacecraft;

interface PirateReactionInterface
{
    public function checkForPirateReaction(
        Spacecraft $target,
        PirateReactionTriggerEnum $reactionTrigger,
        Spacecraft $triggerSpacecraft
    ): bool;

    public function react(
        PirateFleetBattleParty $pirateFleetBattleParty,
        PirateReactionTriggerEnum $reactionTrigger,
        Spacecraft $triggerSpacecraft,
        PirateReactionMetadata $reactionMetadata
    ): void;
}
