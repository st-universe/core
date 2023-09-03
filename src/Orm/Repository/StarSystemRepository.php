<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Database\DatabaseCategoryTypeEnum;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemName;
use Stu\Orm\Entity\StarSystemNameInterface;

/**
 * @extends EntityRepository<StarSystem>
 */
final class StarSystemRepository extends EntityRepository implements StarSystemRepositoryInterface
{
    public function prototype(): StarSystemInterface
    {
        return new StarSystem();
    }

    public function save(StarSystemInterface $storage): void
    {
        $em = $this->getEntityManager();

        $em->persist($storage);
    }

    public function getByLayer(int $layerId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s FROM %s s
                    JOIN %s m
                    WITH m.systems_id = s.id
                    WHERE m.layer_id  = :layerId
                    ORDER BY s.name ASC',
                    StarSystem::class,
                    Map::class
                )
            )
            ->setParameters([
                'layerId' => $layerId
            ])
            ->getResult();
    }

    public function getWithoutDatabaseEntry(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT t FROM %s t WHERE t.database_id NOT IN (SELECT d.id FROM %s d WHERE d.category_id = :categoryId)',
                    StarSystem::class,
                    DatabaseEntry::class
                )
            )
            ->setParameters([
                'categoryId' => DatabaseCategoryTypeEnum::DATABASE_CATEGORY_STARSYSTEM,
            ])
            ->getResult();
    }

    public function getNumberOfSystemsToGenerate(LayerInterface $layer): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(m.id) from %s m
                    WHERE m.system_type_id IS NOT NULL
                    AND m.systems_id IS NULL
                    AND m.layer = :layer',
                    Map::class
                )
            )
            ->setParameters([
                'layer' => $layer
            ])
            ->getSingleScalarResult();
    }

    public function getRandomFreeSystemName(): StarSystemNameInterface
    {
        $freeNames = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ssm FROM %s ssm
                    WHERE NOT EXISTS (SELECT ss.id
                                        FROM %s ss
                                        WHERE ss.name = ssm.name)',
                    StarSystemName::class,
                    StarSystem::class
                )
            )
            ->getResult();

        shuffle($freeNames);

        return current($freeNames);
    }
}
