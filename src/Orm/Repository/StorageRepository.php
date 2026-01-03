<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Storage>
 */
final class StorageRepository extends EntityRepository implements StorageRepositoryInterface
{
    #[\Override]
    public function prototype(): Storage
    {
        return new Storage();
    }

    #[\Override]
    public function save(Storage $storage): void
    {
        $em = $this->getEntityManager();

        $em->persist($storage);
    }

    #[\Override]
    public function delete(Storage $storage): void
    {
        $em = $this->getEntityManager();

        $em->remove($storage);
    }

    #[\Override]
    public function getByUserAccumulated(User $user): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT s.commodity_id AS commodity_id, SUM(s.count) AS amount
                FROM stu_storage s
                JOIN stu_commodity g
                ON s.commodity_id = g.id
                WHERE s.user_id = :userId
                GROUP BY s.commodity_id, g.sort
                ORDER BY g.sort ASC',
                $rsm
            )
            ->setParameters([
                'userId' => $user->getId()
            ])
            ->getResult();
    }

    #[\Override]
    public function getColonyStorageByUserAndCommodity(User $user, int $commodityId): array
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
            'userId' => $user->getId(),
            'commodityId' => $commodityId
        ])->getResult();
    }

    #[\Override]
    public function getSpacecraftStorageByUserAndCommodity(User $user, int $commodityId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('spacecraft_id', 'spacecraft_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT s.commodity_id AS commodity_id, s.spacecraft_id AS spacecraft_id, s.count AS amount
                FROM stu_storage s
                LEFT JOIN stu_commodity g ON g.id = s.commodity_id
                WHERE s.user_id = :userId
                AND s.spacecraft_id IS NOT NULL
                AND s.commodity_id = :commodityId
                ORDER BY s.count DESC',
                $rsm
            )
            ->setParameters([
                'userId' => $user->getId(),
                'commodityId' => $commodityId
            ])
            ->getResult();
    }

    #[\Override]
    public function getTradePostStorageByUserAndCommodity(User $user, int $commodityId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s
                    FROM %s s
                    WHERE s.commodity_id = :commodityId
                    AND s.user = :user
                    AND s.tradepost_id IS NOT NULL
                    ORDER BY s.count DESC',
                    Storage::class
                )
            )
            ->setParameters([
                'commodityId' => $commodityId,
                'user' => $user
            ])
            ->getResult();
    }

    #[\Override]
    public function getTradeOfferStorageByUserAndCommodity(User $user, int $commodityId): array
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
            'userId' => $user->getId(),
            'commodityId' => $commodityId
        ])->getResult();
    }

    #[\Override]
    public function getTorpdeoStorageByUserAndCommodity(User $user, int $commodityId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('spacecraft_id', 'spacecraft_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT s.commodity_id AS commodity_id, ts.spacecraft_id as spacecraft_id,
                SUM(s.count) AS amount
                FROM stu_storage s
                JOIN stu_torpedo_storage ts
                ON s.torpedo_storage_id = ts.id
                WHERE s.user_id = :userId
                AND s.commodity_id = :commodityId
                AND s.torpedo_storage_id IS NOT NULL
                GROUP BY s.commodity_id, ts.spacecraft_id
                ORDER BY amount DESC',
            $rsm
        )->setParameters([
            'userId' => $user->getId(),
            'commodityId' => $commodityId
        ])->getResult();
    }

    #[\Override]
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

    #[\Override]
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

    #[\Override]
    public function getByTradepostAndUserAndCommodity(
        int $tradePostId,
        int $userId,
        int $commodityId
    ): ?Storage {
        return $this->findOneBy([
            'tradepost_id' => $tradePostId,
            'user_id' => $userId,
            'commodity_id' => $commodityId
        ]);
    }

    #[\Override]
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
                        s.tradepost_id IN (SELECT tp.id FROM %s tp WHERE tp.tradeNetwork = :tradeNetwork) AND
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

    #[\Override]
    public function getByTradePost(int $tradePostId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s FROM %s s
                    WHERE s.tradepost_id = :tradePostId
                        OR s.tradeoffer_id IN (SELECT o.id FROM %s o WHERE o.posts_id = :tradePostId)',
                    Storage::class,
                    TradeOffer::class
                )
            )
            ->setParameters([
                'tradePostId' => $tradePostId
            ])
            ->getResult();
    }

    #[\Override]
    public function getLatinumTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT s.user_id, sum(count) as amount
            FROM stu_storage s
            WHERE s.commodity_id = :latId AND s.user_id > :firstUserId
            GROUP BY s.user_id
            ORDER BY 2 DESC
            LIMIT 10',
            $rsm
        )->setParameters([
            'latId' => CommodityTypeConstants::COMMODITY_LATINUM,
            'firstUserId' => UserConstants::USER_FIRST_ID
        ])->getResult();
    }

    #[\Override]
    public function truncateByColony(Colony $colony): void
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

    #[\Override]
    public function truncateByCommodity(int $commodityId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s s WHERE s.commodity_id = :commodityId',
                    Storage::class
                )
            )
            ->setParameter('commodityId', $commodityId)
            ->execute();
    }
}
