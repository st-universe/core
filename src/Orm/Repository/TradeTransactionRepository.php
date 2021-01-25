<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
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
        $em->flush();
    }
}
