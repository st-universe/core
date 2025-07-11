<?php

namespace Stu\Component\Player;

use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Orm\Entity\User;

interface ColonyLimitCalculatorInterface
{
    public function canColonizeFurtherColonyWithType(User $user, ColonyTypeEnum $colonyType): bool;

    public function getColonyLimitWithType(User $user, ColonyTypeEnum $colonyType): int;

    public function getColonyCountWithType(User $user, ColonyTypeEnum $colonyType): int;
}
