<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserProfileVisitorInterface;

interface UserProfileVisitorRepositoryInterface
{
    public function isVisitRegistered(int $profileUserId, int $userId): bool;

    public function prototype(): UserProfileVisitorInterface;

    public function save(UserProfileVisitorInterface $userProfileVisitor): void;

    /**
     * @return UserProfileVisitorInterface[]
     */
    public function getRecent(int $userId): array;

    public function truncateByUser(UserInterface $user): void;
}
