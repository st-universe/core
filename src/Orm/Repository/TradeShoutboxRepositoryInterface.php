<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeShoutbox;

/**
 * @extends ObjectRepository<TradeShoutbox>
 */
interface TradeShoutboxRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<TradeShoutbox>
     */
    public function getByTradeNetwork(int $tradeNetworkId): array;

    public function deleteHistory(int $tradeNetworkId, int $limit = 30): void;

    public function prototype(): TradeShoutbox;

    public function save(TradeShoutbox $tradeShoutbox): void;

    public function truncateByUser(int $userId): void;

    public function truncateAllEntries(): void;
}
