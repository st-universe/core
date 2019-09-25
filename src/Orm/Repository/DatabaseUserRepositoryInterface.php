<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseUserInterface;

interface DatabaseUserRepositoryInterface extends ObjectRepository
{
    public function truncateByUserId(int $userId);

    public function findFor(int $databaseEntryId, int $userId): ?DatabaseUserInterface;

    public function exists(int $userId, int $databaseEntryId): bool;

    public function prototype(): DatabaseUserInterface;

    public function save(DatabaseUserInterface $entry): void;

    public function getTopList(): array;
}