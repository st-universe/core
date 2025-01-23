<?php

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface PirateReactionInterface
{
    public function checkForPirateReaction(
        SpacecraftInterface $target,
        PirateReactionTriggerEnum $reactionTrigger,
        SpacecraftInterface $triggerSpacecraft
    ): bool;

    public function react(
        FleetInterface $fleet,
        PirateReactionTriggerEnum $reactionTrigger,
        SpacecraftInterface $triggerSpacecraft,
        PirateReactionMetadata $reactionMetadata
    ): void;
}
