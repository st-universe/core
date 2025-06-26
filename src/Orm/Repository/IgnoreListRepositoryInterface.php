<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\IgnoreList;

/**
 * @extends ObjectRepository<IgnoreList>
 */
interface IgnoreListRepositoryInterface extends ObjectRepository
{
    public function prototype(): IgnoreList;

    public function save(IgnoreList $ignoreList): void;

    public function delete(IgnoreList $ignoreList): void;

    /**
     * @return list<IgnoreList>
     */
    public function getByRecipient(int $recipientId): array;

    /**
     * @return list<IgnoreList>
     */
    public function getByUser(int $userId): array;

    public function exists(int $userId, int $recipientId): bool;

    public function truncateByUser(int $userId): void;
}
