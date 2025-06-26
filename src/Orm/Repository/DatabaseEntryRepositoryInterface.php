<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseEntry;

/**
 * @extends ObjectRepository<DatabaseEntry>
 *
 * @method null|DatabaseEntry find(integer $id)
 */
interface DatabaseEntryRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<DatabaseEntry>
     */
    public function getByCategoryId(int $categoryId): array;

    public function getByCategoryIdAndObjectId(int $categoryId, int $objectId): ?DatabaseEntry;

    /**
     * @return list<DatabaseEntry>
     */
    public function getStarSystemEntriesByLayer(int $categoryId, ?int $layer = null): array;

    /**
     * @return list<DatabaseEntry>
     */
    public function getRegionEntriesByLayer(int $categoryId, ?int $layer = null): array;

    /**
     * @return list<DatabaseEntry>
     */
    public function getTradePostEntriesByLayer(int $categoryId, ?int $layer = null): array;

    public function prototype(): DatabaseEntry;

    public function save(DatabaseEntry $entry): void;

    /**
     * @return array<int|null>
     */
    public function getDistinctLayerIdsByCategory(int $categoryId): array;
}
