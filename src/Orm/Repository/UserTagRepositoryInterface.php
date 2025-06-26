<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserTag;

/**
 * @extends ObjectRepository<UserTag>
 *
 * @method null|UserTag find(integer $id)
 */
interface UserTagRepositoryInterface extends ObjectRepository
{
    /**
     * @return iterable<UserTag>
     */
    public function getByUser(User $user): iterable;

    public function prototype(): UserTag;

    public function save(UserTag $userTag): void;
}
