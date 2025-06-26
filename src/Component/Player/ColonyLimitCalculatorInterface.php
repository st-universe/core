<?php

namespace Stu\Component\Player;

use Stu\Orm\Entity\User;

interface ColonyLimitCalculatorInterface
{
    public function canColonizeFurtherColonyWithType(User $user, int $colonyType): bool;

    public function getColonyLimitWithType(User $user, int $colonyType): int;

    public function getColonyCountWithType(User $user, int $colonyType): int;
}
