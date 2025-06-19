<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Orm\Entity\UserInterface;

interface PirateProtectionInterface
{
    public function isProtectedAgainstPirates(UserInterface $user): bool;
}
