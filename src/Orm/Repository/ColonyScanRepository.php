<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyScan;
use Stu\Orm\Entity\ColonyScanInterface;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldType;

/**
 * @extends EntityRepository<ColonyScan>
 */
final class ColonyScanRepository extends EntityRepository implements ColonyScanRepositoryInterface
{
    #[Override]
    public function prototype(): ColonyScanInterface
    {
        return new ColonyScan();
    }

    #[Override]
    public function save(ColonyScanInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(ColonyScanInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function truncateByUserId(ColonyScanInterface $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s cs WHERE cs.user_id = :userid',
                    ColonyScan::class
                )
            )
            ->setParameters([
                'userid' => $userId
            ])
            ->execute();
    }

    #[Override]
    public function getSurface(int $colonyId): array
    {
        return $this->getEntityManager()
            ->createQuery(sprintf(
                'SELECT c.field_id, c.type_id, c.buildings_id
                FROM %s c
                WHERE c.colonies_id = :colonyid 
                AND c.type_id NOT IN (SELECT cf.field_id FROM %s cf WHERE cf.category = 3)
                ORDER BY c.field_id ASC',
                PlanetField::class,
                PlanetFieldType::class
            ))
            ->setParameters([
                'colonyid' => $colonyId
            ])
            ->getResult();
    }

    #[Override]
    public function getSurfaceArray(int $id): string
    {
        return strval($this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT cs.mask
                    FROM %s cs
                    WHERE cs.id = :id',
                    ColonyScan::class
                )
            )
            ->setParameters([
                'id' => $id
            ])
            ->getSingleScalarResult());
    }

    #[Override]
    public function getSurfaceWidth(int $id): int
    {
        return (int)$this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c.surface_width
                    FROM %s c
                    JOIN %s cs WITH c.id = cs.colony_id
                    WHERE cs.id = :id',
                    Colony::class,
                    ColonyScan::class
                )
            )
            ->setParameters([
                'id' => $id
            ])
            ->getSingleScalarResult();
    }

    #[Override]
    public function truncateAllColonyScans(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s cs',
                ColonyScan::class
            )
        )->execute();
    }
}
