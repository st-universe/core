<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\DealsInterface;

/**
 * @extends ObjectRepository<Deals>
 *
 * @method null|DealsInterface find(integer $id)
 */
interface DealsRepositoryInterface extends ObjectRepository
{
    public function prototype(): DealsInterface;

    public function save(DealsInterface $post): void;

    public function delete(DealsInterface $post): void;

    public function hasActiveDeals(int $userId): bool;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveDealsGoods(int $userId): ?array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveDealsShips(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveDealsBuildplans(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveDealsGoodsPrestige(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveDealsShipsPrestige(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveDealsBuildplansPrestige(int $userId): array;

    public function hasActiveAuctions(int $userId): bool;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveAuctionsGoods(int $userId): ?array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveAuctionsShips(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveAuctionsBuildplans(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveAuctionsGoodsPrestige(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveAuctionsShipsPrestige(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getActiveAuctionsBuildplansPrestige(int $userId): array;

    public function hasEndedAuctions(int $userId): bool;

    public function hasOwnAuctionsToTake(int $userId): bool;

    /**
     * @return list<DealsInterface>
     */
    public function getEndedAuctionsGoods(int $userId): ?array;

    /**
     * @return list<DealsInterface>
     */
    public function getEndedAuctionsShips(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getEndedAuctionsBuildplans(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getEndedAuctionsGoodsPrestige(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getEndedAuctionsShipsPrestige(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getEndedAuctionsBuildplansPrestige(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getOwnEndedAuctionsGoods(int $userId): ?array;

    /**
     * @return list<DealsInterface>
     */
    public function getOwnEndedAuctionsShips(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getOwnEndedAuctionsBuildplans(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getOwnEndedAuctionsGoodsPrestige(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getOwnEndedAuctionsShipsPrestige(int $userId): array;

    /**
     * @return list<DealsInterface>
     */
    public function getOwnEndedAuctionsBuildplansPrestige(int $userId): array;
}
