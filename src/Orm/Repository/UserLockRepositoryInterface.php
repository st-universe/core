<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserLock;
use Stu\Orm\Entity\UserLockInterface;

/**
 * @extends ObjectRepository<UserLock>
 *
 * @method null|UserLockInterface find(integer $id)
 */
interface UserLockRepositoryInterface extends ObjectRepository
{
    public function getActiveByUser(int $userId): ?UserLockInterface;

    /**
     * @return UserLockInterface[]
     */
    public function getActive(): array;

    public function save(UserLockInterface $researched): void;

    public function prototype(): UserLockInterface;
}
