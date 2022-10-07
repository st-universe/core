<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\StorageInterface;

/**
 * @method null|StorageInterface find(integer $id)
 */
interface StorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): StorageInterface;

    public function save(StorageInterface $storage): void;

    public function delete(StorageInterface $storage): void;

    public function getByUserAccumulated(int $userId): iterable;

    public function getColonyStorageByUserAndCommodity(int $userId, int $commodityId): iterable;

    public function getShipStorageByUserAndCommodity(int $userId, int $commodityId): iterable;

    /**
     * @return StorageInterface[]
     */
    public function getTradePostStorageByUserAndCommodity(int $userId, int $commodityId): array;

    public function getTradeOfferStorageByUserAndCommodity(int $userId, int $commodityId): iterable;

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

    public function getLatinumTop10(): array;

    public function truncateByColony(ColonyInterface $colony): void;
}
