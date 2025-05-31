<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;

/**
 * @extends EntityRepository<DatabaseEntry>
 */
final class DatabaseEntryRepository extends EntityRepository implements DatabaseEntryRepositoryInterface
{
    #[Override]
    public function getByCategoryId(int $categoryId): array
    {
        return $this->findBy([
            'category_id' => $categoryId
        ]);
    }

    #[Override]
    public function getByCategoryIdAndObjectId(int $categoryId, int $objectId): ?DatabaseEntryInterface
    {
        return $this->findOneBy([
            'category_id' => $categoryId,
            'object_id' => $objectId
        ]);
    }

    #[Override]
    public function getStarSystemEntriesByLayer(int $categoryId, ?int $layer = null): array
    {
        $query = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT de FROM %s de WHERE de.category_id = :categoryId AND de.id IN (
                        SELECT ss.database_id FROM %s ss WHERE ss.id IN (
                            SELECT m.systems_id FROM %s m WHERE m.id IN (
                                SELECT l.id FROM %s l WHERE l.layer_id = :layerId
                            )
                        )
                    )',
                    DatabaseEntry::class,
                    StarSystem::class,
                    Map::class,
                    Location::class
                )
            )
            ->setParameter('categoryId', $categoryId);

        if ($layer !== null) {
            $query->setParameter('layerId', $layer);
        } else {
            return $this->findBy(['category_id' => $categoryId]);
        }

        return $query->getResult();
    }

    #[Override]
    public function getRegionEntriesByLayer(int $categoryId, ?int $layer = null): array
    {
        $query = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT de FROM %s de WHERE de.category_id = :categoryId AND de.id IN (
                        SELECT mr.database_id FROM %s mr WHERE mr.id IN (
                            SELECT m.region_id FROM %s m WHERE m.id IN (
                                SELECT l.id FROM %s l WHERE l.layer_id = :layerId
                            )
                        )
                    )',
                    DatabaseEntry::class,
                    MapRegion::class,
                    Map::class,
                    Location::class
                )
            )
            ->setParameter('categoryId', $categoryId);

        if ($layer !== null) {
            $query->setParameter('layerId', $layer);
        } else {
            return $this->findBy(['category_id' => $categoryId]);
        }

        return $query->getResult();
    }

    #[Override]
    public function getTradePostEntriesByLayer(int $categoryId, ?int $layer = null): array
    {
        $query = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT de FROM %s de WHERE de.category_id = :categoryId AND de.object_id IN (
                        SELECT s.id FROM %s s WHERE s.location_id IN (
                                SELECT l.id FROM %s l WHERE l.layer_id = :layerId
                            
                        )
                    )',
                    DatabaseEntry::class,
                    Spacecraft::class,
                    Location::class
                )
            )
            ->setParameter('categoryId', $categoryId);

        if ($layer !== null) {
            $query->setParameter('layerId', $layer);
        } else {
            return $this->findBy(['category_id' => $categoryId]);
        }

        return $query->getResult();
    }

    #[Override]
    public function prototype(): DatabaseEntryInterface
    {
        return new DatabaseEntry();
    }

    #[Override]
    public function save(DatabaseEntryInterface $entry): void
    {
        $em = $this->getEntityManager();

        $em->persist($entry);
    }

    /**
     * @return array<int|null>
     */
    public function getDistinctLayerIdsByCategory(int $categoryId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT DISTINCT d.layer_id FROM %s d WHERE d.category_id = :categoryId',
                    DatabaseEntry::class
                )
            )
            ->setParameter('categoryId', $categoryId)
            ->getSingleColumnResult();
    }
}
