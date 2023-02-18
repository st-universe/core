<?php

declare(strict_types=1);

namespace Stu\Component\Player;

use Stu\Orm\Entity\UserInterface;

interface CrewLimitCalculatorInterface
{
    public function getGlobalCrewLimit(UserInterface $user): int;
}
