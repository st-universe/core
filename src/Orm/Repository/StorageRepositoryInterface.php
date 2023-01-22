<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\StorageInterface;

/**
 * @extends ObjectRepository<Storage>
 *
 * @method null|StorageInterface find(integer $id)
 */
interface StorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): StorageInterface;

    public function save(StorageInterface $storage): void;

    public function delete(StorageInterface $storage): void;

    /**
     * @return iterable<array{commodity_id: int, amount: int}>
     */
    public function getByUserAccumulated(int $userId): iterable;

    /**
     * @return iterable<array{commodity_id: int, colonies_id: int, amount: int}>
     */
    public function getColonyStorageByUserAndCommodity(int $userId, int $commodityId): iterable;

    /**
     * @return iterable<array{commodity_id: int, ships_id: int, amount: int}>
     */
    public function getShipStorageByUserAndCommodity(int $userId, int $commodityId): iterable;

    /**
     * @return StorageInterface[]
     */
    public function getTradePostStorageByUserAndCommodity(int $userId, int $commodityId): array;

    /**
     * @return iterable<array{commodity_id: int, posts_id: int, amount: int}>
     */
    public function getTradeOfferStorageByUserAndCommodity(int $userId, int $commodityId): iterable;

    /**
     * @return iterable<array{commodity_id: int, ship_id: int, amount: int}>
     */
    public function getTorpdeoStorageByUserAndCommodity(int $userId, int $commodityId): iterable;

    /**
     * @return StorageInterface[]
     */
    public function getByTradePostAndUser(int $tradePostId, int $userId): array;

    public function getSumByTradePostAndUser(int $tradePostId, int $userId): int;

    public function getByTradepostAndUserAndCommodity(
        int $tradePostId,
        int $userId,
        int $commodityId
    ): ?StorageInterface;

    /**
     * @return StorageInterface[]
     */
    public function getByTradeNetworkAndUserAndCommodityAmount(
        int $tradeNetwork,
        int $userId,
        int $commodityId,
        int $amount
    ): array;

    /**
     * @return StorageInterface[]
     */
    public function getByTradePost(int $tradePostId): array;

    /**
     * @return array<array{user_id: int, amount: int}>
     */
    public function getLatinumTop10(): array;

    public function truncateByColony(ColonyInterface $colony): void;

    public function truncateByCommodity(int $commodityId): void;
}
