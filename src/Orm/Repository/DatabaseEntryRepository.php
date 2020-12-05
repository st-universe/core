<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\DatabaseEntryInterface;

final class DatabaseEntryRepository extends EntityRepository implements DatabaseEntryRepositoryInterface
{
    public function getByCategoryId(int $categoryId): array
    {
        return $this->findBy([
            'category_id' => $categoryId
        ]);
    }

    public function getByCategoryIdAndObjectId(int $categoryId, int $objectId): DatabaseEntryInterface
    {
        return $this->findOneBy([
            'category_id' => $categoryId,
            'object_id' => $objectId
        ]);
    }

    public function prototype(): DatabaseEntryInterface
    {
        return new DatabaseEntry();
    }

    public function save(DatabaseEntryInterface $entry): void
    {
        $em = $this->getEntityManager();

        $em->persist($entry);
        $em->flush();
    }
}