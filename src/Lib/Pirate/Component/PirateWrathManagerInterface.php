<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Module\Control\GameControllerInterface;

interface PirateWrathManagerInterface
{
    public function increaseWrath(UserInterface $user, PirateReactionTriggerEnum $reactionTrigger): void;

    public function decreaseWrath(UserInterface $user, int $amount): void;

    public function setProtectionTimeoutFromPrestige(UserInterface $user, int $prestige, GameControllerInterface $game): void;
}
