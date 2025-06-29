<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLock;

/**
 * @extends ObjectRepository<UserLock>
 *
 * @method null|UserLock find(integer $id)
 */
interface UserLockRepositoryInterface extends ObjectRepository
{
    public function getActiveByUser(User $user): ?UserLock;

    /**
     * @return list<UserLock>
     */
    public function getActive(): array;

    public function save(UserLock $researched): void;

    public function prototype(): UserLock;
}
