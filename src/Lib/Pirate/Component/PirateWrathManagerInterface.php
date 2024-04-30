<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Orm\Entity\UserInterface;

interface PirateWrathManagerInterface
{
    public function increaseWrath(UserInterface $user, PirateReactionTriggerEnum $reactionTrigger): void;

    public function decreaseWrath(UserInterface $user, int $amount): void;
}
