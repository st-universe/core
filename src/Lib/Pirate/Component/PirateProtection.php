<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Orm\Entity\User;

class PirateProtection implements PirateProtectionInterface
{
    #[\Override]
    public function isProtectedAgainstPirates(User $user): bool
    {
        $pirateWrath = $user->getPirateWrath();
        if ($pirateWrath === null) {
            return false;
        }

        $timeout = $pirateWrath->getProtectionTimeout();
        if ($timeout === null) {
            return false;
        }

        return $timeout > time();
    }
}
