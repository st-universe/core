<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\TradeStorage;
use Stu\Orm\Entity\TradeStorageInterface;

final class TradeStorageRepository extends EntityRepository implements TradeStorageRepositoryInterface
{

    public function prototype(): TradeStorageInterface
    {
        return new TradeStorage();
    }

    public function save(TradeStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    public function delete(TradeStorageInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s ts WHERE ts.user_id = :userId',
                    TradeStorage::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }

    public function getSumByTradePostAndUser(int $tradePostId, int $userId): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT SUM(ts.count) FROM %s ts WHERE ts.posts_id = :tradePostId AND ts.user_id = :userId',
                    TradeStorage::class
                )
            )
            ->setParameters([
                'tradePostId' => $tradePostId,
                'userId' => $userId
            ])
            ->getSingleScalarResult();
    }

    public function getByTradeNetworkAndUserAndCommodityAmount(
        int $tradeNetwork,
        int $userId,
        int $commodityId,
        int $amount
    ): ?TradeStorageInterface {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ts FROM %s ts WHERE
                        ts.posts_id IN (SELECT tp.id FROM %s tp WHERE tp.trade_network = :tradeNetwork) AND
                        ts.user_id = :userId AND ts.goods_id = :commodityId AND ts.count >= :amount
                    ',
                    TradeStorage::class,
                    TradePost::class
                )
            )
            ->setParameters([
                'tradeNetwork' => $tradeNetwork,
                'userId' => $userId,
                'commodityId' => $commodityId,
                'amount' => $amount
            ])
            ->getOneOrNullResult();
    }

    public function getByTradepostAndUserAndCommodity(
        int $tradePostId,
        int $userId,
        int $commodityId
    ): ?TradeStorageInterface {
        return $this->findOneBy([
            'posts_id' => $tradePostId,
            'user_id' => $userId,
            'goods_id' => $commodityId
        ]);
    }

    public function getByTradePostAndUser(int $tradePostId, int $userId): array
    {
        return $this->findBy([
            'posts_id' => $tradePostId,
            'user_id' => $userId
        ], ['goods_id' => 'ASC']);
    }
}
