<?php

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\FleetInterface;

interface PirateReactionInterface
{
    public function react(FleetInterface $fleet, PirateReactionTriggerEnum $reactionTrigger): void;
}
