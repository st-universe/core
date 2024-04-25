<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;

interface PirateBehaviourInterface
{
    public function action(FleetWrapperInterface $fleet, PirateReactionInterface $pirateReaction): void;
}
