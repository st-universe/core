<?php

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Spacecraft;

interface PirateReactionInterface
{
    public function checkForPirateReaction(
        Spacecraft $target,
        PirateReactionTriggerEnum $reactionTrigger,
        Spacecraft $triggerSpacecraft
    ): bool;

    public function react(
        Fleet $fleet,
        PirateReactionTriggerEnum $reactionTrigger,
        Spacecraft $triggerSpacecraft,
        PirateReactionMetadata $reactionMetadata
    ): void;
}
