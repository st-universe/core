<?php

namespace Stu\Component\Player;

use Stu\Orm\Entity\UserInterface;

interface ColonyLimitCalculatorInterface
{
    public function canColonizeFurtherColonyWithType(UserInterface $user, int $colonyType): bool;

    public function getColonyLimitWithType(UserInterface $user, int $colonyType): int;

    public function getColonyCountWithType(UserInterface $user, int $colonyType): int;
}
