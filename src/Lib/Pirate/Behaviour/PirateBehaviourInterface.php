<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface PirateBehaviourInterface
{
    /** @return PirateBehaviourEnum alternative behaviour */
    public function action(
        FleetWrapperInterface $fleet,
        PirateReactionInterface $pirateReaction,
        ?ShipInterface $triggerShip
    ): ?PirateBehaviourEnum;
}
