<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserAward;
use Stu\Orm\Entity\UserAwardInterface;

/**
 * @extends ObjectRepository<UserAward>
 *
 * @method null|UserAwardInterface find(integer $id)
 */
interface UserAwardRepositoryInterface extends ObjectRepository
{
    public function save(UserAwardInterface $researched): void;

    public function delete(UserAwardInterface $researched): void;

    public function prototype(): UserAwardInterface;
}
