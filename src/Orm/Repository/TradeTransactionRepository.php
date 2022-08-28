<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\TradeTransaction;
use Stu\Orm\Entity\TradeTransactionInterface;

final class TradeTransactionRepository extends EntityRepository implements TradeTransactionRepositoryInterface
{
    public function prototype(): TradeTransactionInterface
    {
        return new TradeTransaction();
    }

    public function save(TradeTransactionInterface $tradeTransaction): void
    {
        $em = $this->getEntityManager();

        $em->persist($tradeTransaction);
    }

    public function getLatestTransactions(int $offered, int $wanted): array
    {
        return $this->findBy(
            ['gg_id' => $offered, 'wg_id' => $wanted],
            10
        );
    }

    public function getTradePostsTop10(): array
    {
        $time = time() - 604800;
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('name', 'name', 'string');
        $rsm->addScalarResult('transactions', 'transactions', 'integer');
        return $this->getEntityManager()->createNativeQuery(
            'SELECT tp.name, COUNT(tt.tradepost_id) as transactions FROM stu_trade_transaction tt LEFT JOIN
            stu_trade_posts tp ON tp.id = tt.tradepost_id WHERE tt.date > :sevendays AND tt.tradepost_id > 0 GROUP BY tp.name ORDER BY transactions DESC LIMIT 10',
            $rsm
        )
            ->setParameters([
                'sevendays' => $time
            ])
            ->getArrayResult();
    }
}