<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\DatabaseEntryInterface;

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
}
