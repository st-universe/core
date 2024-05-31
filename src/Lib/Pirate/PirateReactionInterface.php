<?php

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;

interface PirateReactionInterface
{
    public function checkForPirateReaction(
        ShipInterface $target,
        PirateReactionTriggerEnum $reactionTrigger,
        ShipInterface $triggerShip
    ): bool;

    public function react(
        FleetInterface $fleet,
        PirateReactionTriggerEnum $reactionTrigger,
        ShipInterface $triggerShip,
        PirateReactionMetadata $reactionMetadata
    ): void;
}
