<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\DatabaseEntryInterface;

/**
 * @extends ObjectRepository<DatabaseEntry>
 *
 * @method null|DatabaseEntryInterface find(integer $id)
 */
interface DatabaseEntryRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<DatabaseEntryInterface>
     */
    public function getByCategoryId(int $categoryId): array;

    public function getByCategoryIdAndObjectId(int $categoryId, int $objectId): ?DatabaseEntryInterface;

    public function prototype(): DatabaseEntryInterface;

    public function save(DatabaseEntryInterface $entry): void;

    public function getById(int $Id): ?DatabaseEntryInterface;
}
