<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseUser;
use Stu\Orm\Entity\DatabaseUserInterface;

/**
 * @extends ObjectRepository<DatabaseUser>
 */
interface DatabaseUserRepositoryInterface extends ObjectRepository
{
    public function truncateByUserId(int $userId): void;

    public function findFor(int $databaseEntryId, int $userId): ?DatabaseUserInterface;

    public function exists(int $userId, int $databaseEntryId): bool;

    public function prototype(): DatabaseUserInterface;

    public function save(DatabaseUserInterface $entry): void;

    /**
     * @return array<array{user_id: int, points: int}>
     */
    public function getTopList(): array;

    public function getCountForUser(int $userId): int;

    public function hasUserCompletedCategory(int $userId, int $categoryId): bool;

    public function truncateAllEntries(): void;
}
