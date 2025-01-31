<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\UserInterface;

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
     * @return list<array{commodity_id: int, amount: int}>
     */
    public function getByUserAccumulated(UserInterface $user): array;

    /**
     * @return list<array{commodity_id: int, colonies_id: int, amount: int}>
     */
    public function getColonyStorageByUserAndCommodity(UserInterface $user, int $commodityId): array;

    /**
     * @return list<array{commodity_id: int, spacecraft_id: int, amount: int}>
     */
    public function getSpacecraftStorageByUserAndCommodity(UserInterface $user, int $commodityId): array;

    /**
     * @return list<StorageInterface>
     */
    public function getTradePostStorageByUserAndCommodity(UserInterface $user, int $commodityId): array;

    /**
     * @return array<array{commodity_id: int, posts_id: int, amount: int}>
     */
    public function getTradeOfferStorageByUserAndCommodity(UserInterface $user, int $commodityId): array;

    /**
     * @return array<array{commodity_id: int, spacecraft_id: int, amount: int}>
     */
    public function getTorpdeoStorageByUserAndCommodity(UserInterface $user, int $commodityId): array;

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

    public function truncateAllStorages(): void;
}
