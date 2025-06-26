<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Lib;

use Stu\Orm\Entity\User;

interface ProfileVisitorRegistrationInterface
{
    /**
     * Adds a profile visit entry if user and visitor are not the same
     */
    public function register(User $user, User $visitor): void;
}
