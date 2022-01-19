<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\ShipCrew;

final class CrewRepository extends EntityRepository implements CrewRepositoryInterface
{
    public function prototype(): CrewInterface
    {
        return new Crew();
    }

    public function save(CrewInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(CrewInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        //$em->flush();
    }

    public function getAmountByUserAndShipRumpCategory(int $userId, int $shipRumpCategoryId): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount');

        return (int) $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COUNT(c.id) as amount FROM stu_crew c WHERE c.user_id = :userId AND c.id IN (
                    SELECT crew_id FROM stu_ships_crew WHERE ships_id IN (
                        SELECT id FROM stu_ships WHERE rumps_id IN (
                            SELECT id FROM stu_rumps WHERE category_id = :categoryId
                        )
                    )
                )',
                $rsm
            )
            ->setParameters([
                'userId' => $userId,
                'categoryId' => $shipRumpCategoryId
            ])
            ->getSingleScalarResult();
    }

    public function getFreeAmountByUser(int $userId): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT COUNT(c.id) FROM %s c WHERE c.user_id = :userId AND c.id NOT IN (
                        SELECT sc.crew_id FROM %s sc WHERE sc.user_id = :userId
                    )',
                    Crew::class,
                    ShipCrew::class
                )
            )
            ->setParameters(['userId' => $userId])
            ->getSingleScalarResult();
    }

    public function getFreeByUserAndType(int $userId, int $typeId, int $maxAmount): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c WHERE c.user_id = :userId AND c.type = :typeId AND c.id NOT IN (
                        SELECT sc.crew_id FROM %s sc WHERE sc.user_id = :userId
                    )',
                    Crew::class,
                    ShipCrew::class
                )
            )
            ->setMaxResults($maxAmount)
            ->setParameters(['userId' => $userId, 'typeId' => $typeId])
            ->getResult();
    }

    public function getFreeByUser(int $userId, int $maxAmount): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT c FROM %s c WHERE c.user_id = :userId AND c.id NOT IN (
                        SELECT sc.crew_id FROM %s sc WHERE sc.user_id = :userId
                    )',
                    Crew::class,
                    ShipCrew::class
                )
            )
            ->setMaxResults($maxAmount)
            ->setParameters(['userId' => $userId])
            ->getResult();
    }

    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s c WHERE c.user_id = :userId',
                    Crew::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }
}
