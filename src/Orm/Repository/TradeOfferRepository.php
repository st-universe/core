<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Trade\TradeEnum;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradeOfferInterface;

final class TradeOfferRepository extends EntityRepository implements TradeOfferRepositoryInterface
{

    public function prototype(): TradeOfferInterface
    {
        return new TradeOffer();
    }

    public function save(TradeOfferInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(TradeOfferInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function truncateByUser(int $userId): void
    {
        /** @noinspection SyntaxError */
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s to WHERE to.user_id = :userId',
                    TradeOffer::class
                )
            )
            ->setParameter('userId', $userId)
            ->execute();
    }

    public function getByTradePostAndUserAndOfferedCommodity(
        int $tradePostId,
        int $userId,
        int $offeredCommodityId
    ): array {
        return $this->findBy([
            'posts_id' => $tradePostId,
            'user_id' => $userId,
            'gg_id' => $offeredCommodityId
        ]);
    }

    public function getByTradePostAndUserAndCommodities(
        int $tradePostId,
        int $userId,
        int $offeredCommodityId,
        int $wantedCommodityId
    ): array {
        return $this->findBy([
            'posts_id' => $tradePostId,
            'user_id' => $userId,
            'gg_id' => $offeredCommodityId,
            'wg_id' => $wantedCommodityId
        ]);
    }

    public function getByUserLicenses(int $userId, ?int $commodityId, ?int $tradePostId, int $direction): array
    {
        if ($commodityId !== null && $commodityId !== 0) {
            if ($direction === TradeEnum::FILTER_COMMODITY_IN_BOTH) {
                $commoditySql = sprintf(' AND (to.gg_id = %1$d OR to.wg_id = %1$d) ', $commodityId);
            } else if ($direction === TradeEnum::FILTER_COMMODITY_IN_OFFER) {
                $commoditySql = sprintf(' AND to.gg_id = %d ', $commodityId);
            } else {
                $commoditySql = sprintf(' AND to.wg_id = %d ', $commodityId);
            }
        } else {
            $commoditySql = '';
        }

        $time = time();
        /** @noinspection SyntaxError */
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT to FROM %s to WHERE to.posts_id IN (
                        SELECT tl.posts_id FROM %s tl WHERE tl.user_id = :userId AND tl.expired > :actime
                    ) %s %s
                    ORDER BY to.date DESC',
                    TradeOffer::class,
                    TradeLicense::class,
                    $commoditySql,
                    $tradePostId != null ? sprintf(' AND to.posts_id = %d ', $tradePostId) : ''
                )
            )
            ->setParameters([
                'userId' => $userId,
                'actime' => $time
            ])
            ->getResult();
    }

    public function getSumByTradePostAndUser(int $tradePostId, int $userId): int
    {
        /** @noinspection SyntaxError */
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT SUM(to.gg_count * to.amount) FROM %s to WHERE to.posts_id = :tradePostId AND to.user_id = :userId',
                    TradeOffer::class
                )
            )
            ->setParameters([
                'tradePostId' => $tradePostId,
                'userId' => $userId
            ])
            ->getSingleScalarResult();
    }

    public function getGroupedSumByTradePostAndUser(int $tradePostId, int $userId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id');
        $rsm->addScalarResult('amount', 'amount');
        $rsm->addScalarResult('commodity_name', 'commodity_name');

        $result = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT tro.gg_id as commodity_id, SUM(tro.gg_count * tro.amount) as amount, c.name as commodity_name
                    FROM stu_trade_offers tro LEFT JOIN stu_goods c ON c.id = tro.gg_id WHERE
                    tro.posts_id = :tradePostId AND tro.user_id = :userId GROUP BY tro.gg_id,c.name,c.sort ORDER BY c.sort',
                $rsm
            )
            ->setParameters([
                'tradePostId' => $tradePostId,
                'userId' => $userId
            ])
            ->getArrayResult();
        return $result;
    }

    public function getByUserAndCommodity(int $userId, int $commodityId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('commodity_id', 'commodity_id', 'integer');
        $rsm->addScalarResult('posts_id', 'posts_id', 'integer');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT tro.gg_id AS commodity_id, tro.posts_id as posts_id, SUM(tro.amount * tro.gg_count) AS amount
            FROM stu_trade_offers tro
            WHERE tro.user_id = :userId
            and tro.gg_id = :commodityId
            GROUP BY tro.gg_id, tro.posts_id
            ORDER BY amount DESC',
            $rsm
        )->setParameters([
            'userId' => $userId,
            'commodityId' => $commodityId
        ])->getResult();
    }

    public function getOldOffers(int $threshold): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT to FROM %s to
                     WHERE to.date < :maxAge
                     AND to.user_id > 100
                     ORDER BY to.user_id ASC, to.posts_id ASC
                    ',
                    TradeOffer::class
                )
            )
            ->setParameters([
                'maxAge' => time() - $threshold
            ])
            ->getResult();
    }
}
