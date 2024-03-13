<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyScan;
use Stu\Orm\Entity\ColonyScanInterface;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldType;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<ColonyScan>
 */
final class ColonyScanRepository extends EntityRepository implements ColonyScanRepositoryInterface
{
    public function prototype(): ColonyScanInterface
    {
        return new ColonyScan();
    }

    public function save(ColonyScanInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ColonyScanInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

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

    public function getEntryByUserAndSystem(int $user, int $systemId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s
                    FROM %s s 
                    JOIN %s c WITH c.id = s.colony_id 
                    WHERE (s.user_id IN (SELECT u.id FROM %s u WHERE u.allys_id IS NOT NULL AND u.allys_id = (SELECT uu.allys_id FROM %s uu WHERE uu.id = :user)) OR s.user_id = :user) 
                    AND s.colony_id IN (SELECT co.id FROM %s co JOIN %s sm WITH co.starsystem_map_id = sm.id WHERE sm.systems_id = :systemId) ORDER BY s.colony_id, s.date ASC',
                    ColonyScan::class,
                    Colony::class,
                    User::class,
                    User::class,
                    Colony::class,
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'user' => $user,
                'systemId' => $systemId
            ])
            ->getResult();
    }

    public function getEntryByUserAndVisitor(int $user, int $visiteduser): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s
                    FROM %s s            
                    WHERE (s.user_id IN (SELECT u.id FROM %s u WHERE u.allys_id IS NOT NULL AND u.allys_id = (SELECT uu.allys_id FROM %s uu WHERE uu.id = :user)) OR s.user_id = :user) 
                    AND s.colony_user_id IN (SELECT co.colony_user_id FROM %s co WHERE co.colony_user_id = :visiteduser) ORDER BY s.colony_id, s.date ASC',
                    ColonyScan::class,
                    User::class,
                    User::class,
                    ColonyScan::class
                )
            )
            ->setParameters([
                'user' => $user,
                'visiteduser' => $visiteduser
            ])
            ->getResult();
    }


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
