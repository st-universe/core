<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeStorageInterface;

/**
 * @method null|TradeStorageInterface find(integer $id)
 */
interface TradeStorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeStorageInterface;

    public function save(TradeStorageInterface $post): void;

    public function delete(TradeStorageInterface $post): void;

    public function truncateByUser(int $userId): void;

    public function getSumByTradePostAndUser(int $tradePostId, int $userId): int;

    /**
     * @return TradeStorageInterface[]
     */
    public function getByTradeNetworkAndUserAndCommodityAmount(
        int $tradeNetwork,
        int $userId,
        int $commodityId,
        int $amount
    ): array;

    public function getByTradepostAndUserAndCommodity(
        int $tradePostId,
        int $userId,
        int $commodityId
    ): ?TradeStorageInterface;

    /**
     * @return TradeStorageInterface[]
     */
    public function getByTradePostAndUser(int $tradePostId, int $userId): array;

    public function getTradePostUser(int $tradepostId): int;

    public function getByUserAccumulated(int $userId): iterable;

    /**
     * @return TradeStorageInterface[]
     */
    public function getByUserAndCommodity(int $userId, int $commodityId): array;
}