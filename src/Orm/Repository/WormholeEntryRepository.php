<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\WormholeEntryInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapFieldType;

final class WormholeEntryRepository extends EntityRepository implements WormholeEntryRepositoryInterface
{
    public function save(WormholeEntryInterface $entry): void
    {
        $em = $this->getEntityManager();
        $em->persist($entry);
    }

    public function getRandomOuterMap(): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT m.id FROM %s m JOIN %s mf ON mf.id = m.field_id WHERE mf.passable = TRUE ORDER BY RANDOM() LIMIT 1',
                    Map::class,
                    MapFieldType::class
                )
            )
            ->getSingleScalarResult();
    }
}