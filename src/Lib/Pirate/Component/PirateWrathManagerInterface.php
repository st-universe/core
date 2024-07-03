<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\UserInterface;

interface PirateWrathManagerInterface
{
    public function increaseWrathViaTrigger(UserInterface $user, PirateReactionTriggerEnum $reactionTrigger): void;

    public function increaseWrath(UserInterface $user, int $amount): void;

    public function decreaseWrath(UserInterface $user, int $amount): void;

    public function setProtectionTimeoutFromPrestige(UserInterface $user, int $prestige, GameControllerInterface $game): void;
}
