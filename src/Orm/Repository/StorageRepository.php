<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePost;

final class StorageRepository extends EntityRepository implements StorageRepositoryInterface
{
    public function prototype(): StorageInterface
    {
        return new Storage();
    }

    public function save(StorageInterface $storage): void
    {
        $em = $this->getEntityManager();

        $em->persist($storage);
    }

    public function delete(StorageInterface $storage): void
    {
        $em = $this->getEntityManager();

        $em->remove($storage);
    }

    public function getByUserAccumulated(int $userId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT s.commodity_id AS commodity_id, SUM(s.count) AS amount
            FROM stu_storage s
            JOIN stu_commodity g
            ON s.commodity_id = g.id
            WHERE s.user_id = :userId
            GROUP BY s.commodity_id, g.sort
            ORDER BY g.sort ASC',
            $rsm
        )->setParameters([
            'userId' => $userId
        ])->getResult();
    }

    public function getColonyStorageByUserAndCommodity(int $userId, int $commodityId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('colonies_id', 'colonies_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT s.commodity_id AS commodity_id, s.colony_id AS colonies_id, s.count AS amount
            FROM stu_storage s
            WHERE s.user_id = :userId
            AND s.colony_id IS NOT NULL
            AND s.commodity_id = :commodityId
            ORDER BY s.count DESC',
            $rsm
        )->setParameters([
            'userId' => $userId,
            'commodityId' => $commodityId
        ])->getResult();
    }

    public function getShipStorageByUserAndCommodity(int $userId, int $commodityId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('ships_id', 'ships_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT s.commodity_id AS commodity_id, s.ship_id AS ships_id, s.count AS amount
            FROM stu_storage s
            LEFT JOIN stu_commodity g ON g.id = s.commodity_id
            WHERE s.user_id = :userId
            AND s.ship_id IS NOT NULL
            AND s.commodity_id = :commodityId
            ORDER BY s.count DESC',
            $rsm
        )->setParameters([
            'userId' => $userId,
            'commodityId' => $commodityId
        ])->getResult();
    }

    public function getTradePostStorageByUserAndCommodity(int $userId, int $commodityId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s
                FROM %s s
                WHERE s.commodity_id = :commodityId
                AND s.user_id = :userId
                AND s.tradepost_id IS NOT NULL
                ORDER BY s.count DESC',
                Storage::class
            )
        )->setParameters([
            'commodityId' => $commodityId,
            'userId' => $userId
        ])->getResult();
    }

    public function getTradeOfferStorageByUserAndCommodity(int $userId, int $commodityId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('posts_id', 'posts_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT s.commodity_id AS commodity_id, tof.posts_id as posts_id,
                SUM(s.count) AS amount
                FROM stu_storage s
                JOIN stu_trade_offers tof
                ON s.tradeoffer_id = tof.id
                WHERE s.user_id = :userId
                AND s.commodity_id = :commodityId
                AND s.tradeoffer_id IS NOT NULL
                GROUP BY s.commodity_id, tof.posts_id
                ORDER BY amount DESC',
            $rsm
        )->setParameters([
            'userId' => $userId,
            'commodityId' => $commodityId
        ])->getResult();
    }

    public function getTorpdeoStorageByUserAndCommodity(int $userId, int $commodityId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('ship_id', 'ship_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT s.commodity_id AS commodity_id, ts.ship_id as ship_id,
                SUM(s.count) AS amount
                FROM stu_storage s
                JOIN stu_torpedo_storage ts
                ON s.torpedo_storage_id = ts.id
                WHERE s.user_id = :userId
                AND s.commodity_id = :commodityId
                AND s.torpedo_storage_id IS NOT NULL
                GROUP BY s.commodity_id, ts.ship_id
                ORDER BY amount DESC',
            $rsm
        )->setParameters([
            'userId' => $userId,
            'commodityId' => $commodityId
        ])->getResult();
    }

    public function getByTradePostAndUser(int $tradePostId, int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s INDEX BY s.commodity_id
                    WHERE s.tradepost_id = :tradePostId
                    AND s.user_id = :userId
                    ORDER BY s.commodity_id ASC',
                Storage::class
            )
        )->setParameters([
            'tradePostId' => $tradePostId,
            'userId' => $userId
        ])->getResult();
    }

    public function getSumByTradePostAndUser(int $tradePostId, int $userId): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT SUM(s.count) FROM %s s WHERE s.tradepost_id = :tradePostId AND s.user_id = :userId',
                    Storage::class
                )
            )
            ->setParameters([
                'tradePostId' => $tradePostId,
                'userId' => $userId
            ])
            ->getSingleScalarResult();
    }

    public function getByTradepostAndUserAndCommodity(
        int $tradePostId,
        int $userId,
        int $commodityId
    ): ?StorageInterface {
        return $this->findOneBy([
            'tradepost_id' => $tradePostId,
            'user_id' => $userId,
            'commodity_id' => $commodityId
        ]);
    }

    public function getByTradeNetworkAndUserAndCommodityAmount(
        int $tradeNetwork,
        int $userId,
        int $commodityId,
        int $amount
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s FROM %s s WHERE
                        s.tradepost_id IN (SELECT tp.id FROM %s tp WHERE tp.trade_network = :tradeNetwork) AND
                        s.user_id = :userId AND s.commodity_id = :commodityId AND s.count >= :amount
                    ',
                    Storage::class,
                    TradePost::class
                )
            )
            ->setParameters([
                'tradeNetwork' => $tradeNetwork,
                'userId' => $userId,
                'commodityId' => $commodityId,
                'amount' => $amount
            ])
            ->getResult();
    }

    public function getLatinumTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT s.user_id, sum(count) as amount
            FROM stu_storage s
            WHERE s.commodity_id = 50 AND s.user_id > 100
            GROUP BY s.user_id
            ORDER BY 2 DESC
            LIMIT 10',
            $rsm
        )->setParameters([
            'latId' => CommodityTypeEnum::COMMODITY_LATINUM
        ])->getResult();
    }

    public function truncateByColony(ColonyInterface $colony): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s s WHERE s.colony_id = :colony',
                    Storage::class
                )
            )
            ->setParameter('colony', $colony)
            ->execute();
    }
}
