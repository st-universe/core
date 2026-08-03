<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserCrewRank;

/**
 * @extends ObjectRepository<UserCrewRank>
 */
interface UserCrewRankRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserCrewRank;

    public function save(UserCrewRank $userCrewRank): void;

    public function delete(UserCrewRank $userCrewRank): void;

    public function truncateByUser(User $user): void;
}
