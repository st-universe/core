<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserLockInterface;

/**
 * @method null|UserLockInterface find(integer $id)
 */
interface UserLockRepositoryInterface extends ObjectRepository
{
    public function getByUser(int $userId): ?UserLockInterface;

    public function save(UserLockInterface $researched): void;

    public function prototype(): UserLockInterface;
}
