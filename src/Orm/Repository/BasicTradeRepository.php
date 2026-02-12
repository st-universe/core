<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Trade\TradeEnum;
use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradePost;

/**
 * @extends EntityRepository<BasicTrade>
 */
final class BasicTradeRepository extends EntityRepository implements BasicTradeRepositoryInterface
{
    #[\Override]
    public function prototype(): BasicTrade
    {
        return new BasicTrade();
    }

    #[\Override]
    public function save(BasicTrade $basicTrade): void
    {
        $em = $this->getEntityManager();

        $em->persist($basicTrade);
    }

    #[\Override]
    public function getBasicTrades(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT bt FROM %1$s bt
                WHERE bt.faction_id IN (SELECT tp.tradeNetwork
                                        FROM %2$s tl
                                        JOIN %3$s tp WITH tl.posts_id = tp.id
                                        WHERE tl.user_id = :userId
                                        GROUP BY tp.tradeNetwork)
                AND bt.date_ms = (SELECT max(bt2.date_ms) FROM %1$s bt2
                                WHERE bt.faction_id = bt2.faction_id AND bt.commodity_id = bt2.commodity_id)
                ORDER BY bt.commodity_id ASC',
                BasicTrade::class,
                TradeLicense::class,
                TradePost::class
            )
        )->setParameters([
            'userId' => $userId
        ])->getResult();
    }

    #[\Override]
    public function getByUniqId(string $uniqId): ?BasicTrade
    {
        return $this->findOneBy([
            'uniqid' => $uniqId
        ]);
    }

    #[\Override]
    public function isNewest(BasicTrade $basicTrade): bool
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT count(bt.id) FROM %s bt
                WHERE bt.faction_id = :factionId
                AND bt.commodity_id = :commodityId
                AND bt.date_ms > :myDate',
                BasicTrade::class
            )
        )->setParameters([
            'factionId' => $basicTrade->getFaction()->getId(),
            'commodityId' => $basicTrade->getCommodity()->getId(),
            'myDate' => $basicTrade->getDate()
        ])->getSingleScalarResult() === 0;
    }

    #[\Override]
    public function getLatestRates(BasicTrade $basicTrade): array
    {
        return $this->getLatestRatesByAmount($basicTrade, TradeEnum::BASIC_TRADE_LATEST_RATE_AMOUNT);
    }

    #[\Override]
    public function getLatestRatesByAmount(BasicTrade $basicTrade, int $amount): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT bt FROM %s bt
                    WHERE bt.faction_id = :factionId
                    AND bt.commodity_id = :commodityId
                    ORDER BY bt.date_ms DESC',
                    BasicTrade::class
                )
            )
            ->setParameters([
                'factionId' => $basicTrade->getFaction()->getId(),
                'commodityId' => $basicTrade->getCommodity()->getId()
            ])
            ->setMaxResults(max(1, $amount))
            ->getResult();
    }

    #[\Override]
    public function getTradeCount(BasicTrade $basicTrade): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(bt.id) FROM %s bt
                    WHERE bt.faction_id = :factionId
                    AND bt.commodity_id = :commodityId',
                    BasicTrade::class
                )
            )
            ->setParameters([
                'factionId' => $basicTrade->getFaction()->getId(),
                'commodityId' => $basicTrade->getCommodity()->getId()
            ])
            ->getSingleScalarResult();
    }
}
