<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyStorage;
use Stu\Orm\Entity\ColonyStorageInterface;
use Stu\Orm\Entity\Commodity;

final class ColonyStorageRepository extends EntityRepository implements ColonyStorageRepositoryInterface
{

    public function prototype(): ColonyStorageInterface
    {
        return new ColonyStorage();
    }

    public function save(ColonyStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ColonyStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        //$em->flush();
    }

    public function getByColony(int $colonyId, int $viewable = 1): array
    {
        /** @noinspection SyntaxError */
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT cs FROM %s cs INDEX BY cs.goods_id LEFT JOIN %s g WITH g.id = cs.goods_id
                        WHERE cs.colonies_id = :colonyId ORDER BY g.sort',
                    ColonyStorage::class,
                    Commodity::class
                )
            )
            ->setParameters([
                'colonyId' => $colonyId,
            ])
            ->getResult();
    }

    public function getByUserAccumulated(int $userId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT cs.goods_id AS commodity_id, SUM(cs.count) AS amount
            FROM stu_colonies_storage cs
            LEFT JOIN stu_goods g ON g.id = cs.goods_id
            LEFT JOIN stu_colonies c ON cs.colonies_id = c.id
            WHERE c.user_id = :userId
            GROUP BY cs.goods_id
            ORDER BY cs.goods_id ASC',
            $rsm
        )->setParameters([
            'userId' => $userId
        ])->getResult();
    }

    public function getByUserAndCommodity(int $userId, int $commodityId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('colonies_id', 'colonies_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT cs.goods_id AS commodity_id, cs.colonies_id AS colonies_id, cs.count AS amount
            FROM stu_colonies_storage cs
            LEFT JOIN stu_goods g ON g.id = cs.goods_id
            LEFT JOIN stu_colonies c ON cs.colonies_id = c.id
            WHERE c.user_id = :userId
            AND g.id = :commodityId
            ORDER BY cs.count DESC',
            $rsm
        )->setParameters([
            'userId' => $userId,
            'commodityId' => $commodityId
        ])->getResult();
    }

    public function truncateByColony(ColonyInterface $colony): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s cs WHERE cs.colonies_id = :colony',
                    ColonyStorage::class
                )
            )
            ->setParameter('colony', $colony)
            ->execute();
    }
}
