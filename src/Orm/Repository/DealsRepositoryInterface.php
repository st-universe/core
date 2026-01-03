<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Deals;

/**
 * @extends ObjectRepository<Deals>
 *
 * @method null|Deals find(integer $id)
 */
interface DealsRepositoryInterface extends ObjectRepository
{
    public function prototype(): Deals;

    public function save(Deals $post): void;

    public function delete(Deals $post): void;

    public function hasActiveDeals(int $userId): bool;

    /**
     * @return array<Deals>
     */
    public function getActiveDealsGoods(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveDealsShips(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveDealsBuildplans(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveDealsGoodsPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveDealsShipsPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveDealsBuildplansPrestige(int $userId): array;

    public function hasActiveAuctions(int $userId): bool;

    /**
     * @return array<Deals>
     */
    public function getActiveAuctionsGoods(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveAuctionsShips(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveAuctionsBuildplans(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveAuctionsGoodsPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveAuctionsShipsPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getActiveAuctionsBuildplansPrestige(int $userId): array;

    public function hasEndedAuctions(int $userId): bool;

    public function hasOwnAuctionsToTake(int $userId): bool;

    /**
     * @return array<Deals>
     */
    public function getEndedAuctionsGoods(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getEndedAuctionsShips(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getEndedAuctionsBuildplans(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getEndedAuctionsGoodsPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getEndedAuctionsShipsPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getEndedAuctionsBuildplansPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getOwnEndedAuctionsGoods(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getOwnEndedAuctionsShips(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getOwnEndedAuctionsBuildplans(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getOwnEndedAuctionsGoodsPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getOwnEndedAuctionsShipsPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getOwnEndedAuctionsBuildplansPrestige(int $userId): array;

    /**
     * @return array<Deals>
     */
    public function getRecentlyStartedDeals(int $timeThreshold): array;
}
