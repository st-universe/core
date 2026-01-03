<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Fleet>
 */
final class FleetRepository extends EntityRepository implements FleetRepositoryInterface
{
    #[\Override]
    public function prototype(): Fleet
    {
        return new Fleet();
    }

    #[\Override]
    public function save(Fleet $fleet): void
    {
        $em = $this->getEntityManager();

        $em->persist($fleet);
    }

    #[\Override]
    public function delete(Fleet $fleet): void
    {
        $em = $this->getEntityManager();

        $em->remove($fleet);
        $em->flush(); //TODO really neccessary?
    }

    #[\Override]
    public function truncateByUser(User $user): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s f WHERE f.user = :user',
                Fleet::class
            )
        )
            ->setParameters(['user' => $user])
            ->execute();
    }

    #[\Override]
    public function getByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT f FROM %s f
                    JOIN f.user u WHERE u.id = :userId
                    ORDER BY f.sort DESC, f.id DESC',
                    Fleet::class
                )
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    #[\Override]
    public function getCountByUser(int $userId): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(f)
                FROM %s f
                JOIN f.user u
                WHERE u.id = :userId',
                Fleet::class
            )
        )->setParameter('userId', $userId)
            ->getSingleScalarResult();
    }

    #[\Override]
    public function getHighestSortByUser(int $userId): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('newsort', 'newsort');

        return (int)$this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(
                        MAX(
                            CASE
                                WHEN f.sort > f.id THEN f.sort
                                ELSE f.id
                            END
                        ),
                        0
                    ) + 1 as newsort FROM stu_fleets f WHERE f.user_id = :userId',
                $rsm
            )
            ->setParameters([
                'userId' => $userId
            ])
            ->getSingleScalarResult();
    }

    #[\Override]
    public function getNonNpcFleetList(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                'SELECT f FROM %s f JOIN f.user u WHERE u.id > :firstUserId',
                    Fleet::class
                )
            )
            ->setParameter('firstUserId', UserConstants::USER_FIRST_ID)
            ->getResult();
    }
}
