<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Orm\Entity\UserInterface;

interface PirateRoundManagerInterface
{
    public function decreasePrestige(int $amount): void;

    public function addUserStats(UserInterface $user, int $prestige): void;
}
