<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Orm\Entity\User;

interface UserLockCheckerInterface
{
    public function check(User $user): void;
}
