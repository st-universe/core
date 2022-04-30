<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradePost;

final class BasicTradeRepository extends EntityRepository implements BasicTradeRepositoryInterface
{
    public function prototype(): BasicTradeInterface
    {
        return new BasicTrade();
    }

    public function save(BasicTradeInterface $basicTrade): void
    {
        $em = $this->getEntityManager();

        $em->persist($basicTrade);
    }

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
                AND bt.random = (SELECT random FROM %1$s bt2
                                WHERE bt.faction_id = bt2.faction_id AND bt.commodity_id = bt2.commodity_id
                                ORDER BY bt2.date DESC LIMIT 1)
                ORDER BY bt.commodity_id ASC',
                BasicTrade::class,
                TradeLicense::class,
                TradePost::class
            )
        )->setParameters([
            'userId' => $userId
        ])->getResult();
    }
}
