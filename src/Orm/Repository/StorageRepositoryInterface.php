<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Storage>
 *
 * @method null|Storage find(integer $id)
 */
interface StorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): Storage;

    public function save(Storage $storage): void;

    public function delete(Storage $storage): void;

    /**
     * @return list<array{commodity_id: int, amount: int}>
     */
    public function getByUserAccumulated(User $user): array;

    /**
     * @return list<array{commodity_id: int, colonies_id: int, amount: int}>
     */
    public function getColonyStorageByUserAndCommodity(User $user, int $commodityId): array;

    /**
     * @return list<array{commodity_id: int, spacecraft_id: int, amount: int}>
     */
    public function getSpacecraftStorageByUserAndCommodity(User $user, int $commodityId): array;

    /**
     * @return list<Storage>
     */
    public function getTradePostStorageByUserAndCommodity(User $user, int $commodityId): array;

    /**
     * @return array<array{commodity_id: int, posts_id: int, amount: int}>
     */
    public function getTradeOfferStorageByUserAndCommodity(User $user, int $commodityId): array;

    /**
     * @return array<array{commodity_id: int, spacecraft_id: int, amount: int}>
     */
    public function getTorpdeoStorageByUserAndCommodity(User $user, int $commodityId): array;

    /**
     * @return Storage[]
     */
    public function getByTradePostAndUser(int $tradePostId, int $userId): array;

    public function getSumByTradePostAndUser(int $tradePostId, int $userId): int;

    public function getByTradepostAndUserAndCommodity(
        int $tradePostId,
        int $userId,
        int $commodityId
    ): ?Storage;

    /**
     * @return Storage[]
     */
    public function getByTradeNetworkAndUserAndCommodityAmount(
        int $tradeNetwork,
        int $userId,
        int $commodityId,
        int $amount
    ): array;

    /**
     * @return Storage[]
     */
    public function getByTradePost(int $tradePostId): array;

    /**
     * @return array<array{user_id: int, amount: int}>
     */
    public function getLatinumTop10(): array;

    public function truncateByColony(Colony $colony): void;

    public function truncateByCommodity(int $commodityId): void;

    public function truncateAllStorages(): void;
}
