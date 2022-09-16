<?php

namespace Stu\Module\Award\Lib;

use Stu\Orm\Entity\AwardInterface;
use Stu\Orm\Entity\UserInterface;

interface CreateUserAwardInterface
{
    public function createAwardForUser(UserInterface $user, AwardInterface $award): void;
}
