<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserAward;

/**
 * @extends ObjectRepository<UserAward>
 *
 * @method null|UserAward find(integer $id)
 */
interface UserAwardRepositoryInterface extends ObjectRepository
{
    public function save(UserAward $researched): void;

    public function delete(UserAward $researched): void;

    public function prototype(): UserAward;
}
