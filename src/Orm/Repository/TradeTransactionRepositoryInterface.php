<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeTransaction;

/**
 * @extends ObjectRepository<TradeTransaction>
 *
 * @method null|TradeTransaction find(integer $id)
 */
interface TradeTransactionRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeTransaction;

    public function save(TradeTransaction $tradeTransaction): void;

    /**
     * @return list<array{id: int, name: string, transactions: int}>
     */
    public function getTradePostsTop10(): array;

    public function truncateAllTradeTransactions(): void;
}
