<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate\Component;

use Stu\Orm\Entity\User;

interface PirateRoundManagerInterface
{
    public function decreasePrestige(int $amount): void;

    public function addUserStats(User $user, int $prestige): void;
}
