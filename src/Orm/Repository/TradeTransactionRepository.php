<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Orm\Entity\TradeTransaction;

/**
 * @extends EntityRepository<TradeTransaction>
 */
final class TradeTransactionRepository extends EntityRepository implements TradeTransactionRepositoryInterface
{
    #[Override]
    public function prototype(): TradeTransaction
    {
        return new TradeTransaction();
    }

    #[Override]
    public function save(TradeTransaction $tradeTransaction): void
    {
        $em = $this->getEntityManager();

        $em->persist($tradeTransaction);
    }

    #[Override]
    public function getTradePostsTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('transactions', 'transactions', 'integer');
        return $this
            ->getEntityManager()
            ->createNativeQuery(
                'SELECT tp.id, tp.name, COUNT(tt.tradepost_id) as transactions
                FROM stu_trade_transaction tt
                LEFT JOIN stu_trade_posts tp ON tp.id = tt.tradepost_id
                WHERE tt.date > :sevendays AND tt.tradepost_id > 0 AND tp.id > 0
                GROUP BY tp.id ORDER BY transactions DESC LIMIT 10',
                $rsm
            )
            ->setParameters([
                'sevendays' => time() - TimeConstants::SEVEN_DAYS_IN_SECONDS
            ])
            ->getResult();
    }

    #[Override]
    public function truncateAllTradeTransactions(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ta',
                TradeTransaction::class
            )
        )->execute();
    }
}
