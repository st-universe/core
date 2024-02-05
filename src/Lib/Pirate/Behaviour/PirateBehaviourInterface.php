<?php

namespace Stu\Lib\Pirate\Behaviour;

use Stu\Module\Ship\Lib\FleetWrapperInterface;

interface PirateBehaviourInterface
{
    public function action(FleetWrapperInterface $fleet): void;
}
