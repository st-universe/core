<?php

namespace Stu\Module\Award\Lib;

use Stu\Orm\Entity\Award;
use Stu\Orm\Entity\User;

interface CreateUserAwardInterface
{
    public function createAwardForUser(User $user, Award $award): void;
}
