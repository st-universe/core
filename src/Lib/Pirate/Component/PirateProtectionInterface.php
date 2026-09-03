<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate\Component;

use Stu\Orm\Entity\User;

interface PirateProtectionInterface
{
    public function isProtectedAgainstPirates(User $user): bool;
}
