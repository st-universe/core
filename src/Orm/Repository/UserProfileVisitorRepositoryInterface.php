<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserProfileVisitor;
use Stu\Orm\Entity\UserProfileVisitorInterface;

/**
 * @extends ObjectRepository<UserProfileVisitor>
 */
interface UserProfileVisitorRepositoryInterface extends ObjectRepository
{
    public function isVisitRegistered(UserInterface $user, UserInterface $visitor): bool;

    public function prototype(): UserProfileVisitorInterface;

    public function save(UserProfileVisitorInterface $userProfileVisitor): void;

    /**
     * @return list<UserProfileVisitorInterface>
     */
    public function getRecent(int $userId): array;

    public function truncateByUser(UserInterface $user): void;

    public function truncateAllEntries(): void;
}
