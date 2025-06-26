<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserProfileVisitor;

/**
 * @extends ObjectRepository<UserProfileVisitor>
 */
interface UserProfileVisitorRepositoryInterface extends ObjectRepository
{
    public function isVisitRegistered(User $user, User $visitor): bool;

    public function prototype(): UserProfileVisitor;

    public function save(UserProfileVisitor $userProfileVisitor): void;

    /**
     * @return list<UserProfileVisitor>
     */
    public function getRecent(int $userId): array;

    public function truncateByUser(User $user): void;

    public function truncateAllEntries(): void;
}
