<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Trade\TradeEnum;
use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradePost;

/**
 * @extends EntityRepository<BasicTrade>
 */
final class BasicTradeRepository extends EntityRepository implements BasicTradeRepositoryInterface
{
    #[Override]
    public function prototype(): BasicTradeInterface
    {
        return new BasicTrade();
    }

    #[Override]
    public function save(BasicTradeInterface $basicTrade): void
    {
        $em = $this->getEntityManager();

        $em->persist($basicTrade);
    }

    #[Override]
    public function getBasicTrades(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT bt FROM %1$s bt
                WHERE bt.faction_id IN (SELECT tp.trade_network
                                        FROM %2$s tl
                                        JOIN %3$s tp WITH tl.posts_id = tp.id
                                        WHERE tl.user_id = :userId
                                        GROUP BY tp.trade_network)
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

    #[Override]
    public function getByUniqId(string $uniqId): ?BasicTradeInterface
    {
        return $this->findOneBy([
            'uniqid' => $uniqId
        ]);
    }

    #[Override]
    public function isNewest(BasicTradeInterface $basicTrade): bool
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

    #[Override]
    public function getLatestRates(BasicTradeInterface $basicTrade): array
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
            ->setMaxResults(TradeEnum::BASIC_TRADE_LATEST_RATE_AMOUNT)
            ->getResult();
    }

    #[Override]
    public function truncateAllBasicTrades(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s bt',
                BasicTrade::class
            )
        )->execute();
    }
}
