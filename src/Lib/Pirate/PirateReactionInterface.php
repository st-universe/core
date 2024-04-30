<?php

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;

interface PirateReactionInterface
{
    public function react(
        FleetInterface $fleet,
        PirateReactionTriggerEnum $reactionTrigger,
        ShipInterface $triggerShip
    ): void;
}
