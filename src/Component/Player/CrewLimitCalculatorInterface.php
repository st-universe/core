<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Orm\Entity\User;

interface CrewLimitCalculatorInterface
{
    public function getGlobalCrewLimit(User $user): int;
}
