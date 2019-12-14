<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserTagInterface;

/**
 * @method null|UserTagInterface find(integer $id)
 */
interface UserTagRepositoryInterface extends ObjectRepository
{
    /**
     * @return UserTagInterface[]
     */
    public function getByUser(UserInterface $user): iterable;

    public function prototype(): UserTagInterface;

    public function save(UserTagInterface $userTag): void;
}
