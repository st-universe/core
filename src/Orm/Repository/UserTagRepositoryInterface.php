<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\UserTagInterface;

interface UserTagRepositoryInterface
{
    public function prototype(): UserTagInterface;

    public function save(UserTagInterface $userTag): void;
}
