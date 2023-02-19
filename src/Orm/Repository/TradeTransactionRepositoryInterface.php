<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeTransaction;
use Stu\Orm\Entity\TradeTransactionInterface;

/**
 * @extends ObjectRepository<TradeTransaction>
 *
 * @method null|TradeTransactionInterface find(integer $id)
 */
interface TradeTransactionRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeTransactionInterface;

    public function save(TradeTransactionInterface $tradeTransaction): void;

    /**
     * @return list<array{id: int, name: string, transactions: int}>
     */
    public function getTradePostsTop10(): array;
}
