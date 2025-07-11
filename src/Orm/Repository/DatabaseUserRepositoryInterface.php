<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseUser;

/**
 * @extends ObjectRepository<DatabaseUser>
 */
interface DatabaseUserRepositoryInterface extends ObjectRepository
{
    public function truncateByUserId(int $userId): void;

    public function findFor(int $databaseEntryId, int $userId): ?DatabaseUser;

    public function exists(int $userId, int $databaseEntryId): bool;

    public function prototype(): DatabaseUser;

    public function save(DatabaseUser $entry): void;

    /**
     * @return array<array{user_id: int, points: int, timestamp: int}>
     */
    public function getTopList(): array;

    public function getCountForUser(int $userId): int;

    public function hasUserCompletedCategoryAndLayer(int $userId, int $categoryId, ?int $ignoredDatabaseEntryId = null, ?int $layerId = null): bool;
}
