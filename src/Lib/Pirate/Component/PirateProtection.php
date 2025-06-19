<?php

namespace Stu\Lib\Pirate\Component;

use Override;
use Stu\Orm\Entity\UserInterface;

class PirateProtection implements PirateProtectionInterface
{
    #[Override]
    public function isProtectedAgainstPirates(UserInterface $user): bool
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
