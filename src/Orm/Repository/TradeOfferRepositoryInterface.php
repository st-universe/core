<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradeOfferInterface;

/**
 * @extends ObjectRepository<TradeOffer>
 *
 * @method null|TradeOfferInterface find(integer $id)
 */
interface TradeOfferRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeOfferInterface;

    public function save(TradeOfferInterface $post): void;

    public function delete(TradeOfferInterface $post): void;

    public function truncateByUser(int $userId): void;

    /**
     * @return array<TradeOfferInterface>
     */
    public function getByTradePostAndUserAndOfferedCommodity(
        int $tradePostId,
        int $userId,
        int $offeredCommodityId
    ): array;

    /**
     * @return array<TradeOfferInterface>
     */
    public function getByTradePostAndUserAndCommodities(
        int $tradePostId,
        int $userId,
        int $offeredCommodityId,
        int $wantedCommodityId
    ): array;

    /**
     * @return array<TradeOfferInterface>
     */
    public function getByUserLicenses(int $userId, ?int $commodityId, ?int $tradePostId, int $direction): array;

    public function getSumByTradePostAndUser(int $tradePostId, int $userId): int;

    /**
     * @return array<array{commodity_id: int, amount: int, commodity_name: string}>
     */
    public function getGroupedSumByTradePostAndUser(int $tradePostId, int $userId): array;

    /**
     * @return array<TradeOfferInterface>
     */
    public function getOldOffers(int $threshold): array;

    public function truncateAllTradeOffers(): void;
}
