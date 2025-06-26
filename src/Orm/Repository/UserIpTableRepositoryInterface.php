<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserIpTable;

/**
 * @extends ObjectRepository<UserIpTable>
 */
interface UserIpTableRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserIpTable;

    public function save(UserIpTable $userIpTable): void;

    public function findMostRecentByUser(User $user): ?UserIpTable;

    public function findBySessionId(string $sessionId): ?UserIpTable;

    public function truncateAllEntries(): void;
}
