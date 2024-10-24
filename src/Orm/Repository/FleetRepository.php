<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<Fleet>
 */
final class FleetRepository extends EntityRepository implements FleetRepositoryInterface
{
    #[Override]
    public function prototype(): FleetInterface
    {
        return new Fleet();
    }

    #[Override]
    public function save(FleetInterface $fleet): void
    {
        $em = $this->getEntityManager();

        $em->persist($fleet);
    }

    #[Override]
    public function delete(FleetInterface $fleet): void
    {
        $em = $this->getEntityManager();

        $em->remove($fleet);
        $em->flush(); //TODO really neccessary?
    }

    #[Override]
    public function truncateByUser(UserInterface $user): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s f WHERE f.user_id = :user',
                Fleet::class
            )
        )
            ->setParameters(['user' => $user])
            ->execute();
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['sort' => 'desc', 'id' => 'desc']
        );
    }

    #[Override]
    public function getCountByUser(int $userId): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(f)
                FROM %s f
                WHERE f.user_id = :userId',
                Fleet::class
            )
        )->setParameter('userId', $userId)
            ->getSingleScalarResult();
    }

    #[Override]
    public function getHighestSortByUser(int $userId): int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('newsort', 'newsort');

        return (int)$this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(MAX(GREATEST(f.sort, f.id)), 0) + 1 as newsort FROM stu_fleets f WHERE f.user_id = :userId',
                $rsm
            )
            ->setParameters([
                'userId' => $userId
            ])
            ->getSingleScalarResult();
    }

    #[Override]
    public function getNonNpcFleetList(): iterable
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT f FROM %s f WHERE f.user_id > :firstUserId',
                    Fleet::class
                )
            )
            ->setParameter('firstUserId', UserEnum::USER_FIRST_ID)
            ->getResult();
    }

    #[Override]
    public function truncateAllFleets(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s f',
                Fleet::class
            )
        )->execute();
    }
}
