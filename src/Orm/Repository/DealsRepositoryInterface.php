<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DealsInterface;
use Stu\Orm\Entity\TradePostInterface;

/**
 * @method null|DealsInterface find(integer $id)
 */
interface DealsRepositoryInterface extends ObjectRepository
{
    public function prototype(): DealsInterface;

    public function save(DealsInterface $post): void;

    public function delete(DealsInterface $post): void;

    public function getDeals(int $userId): array;

    public function getActiveDeals(int $userId): array;

    public function getActiveDealsGoods(int $userId): ?array;

    public function getActiveDealsShips(int $userId): array;

    public function getActiveDealsBuildplans(int $userId): array;

    public function getActiveDealsGoodsPrestige(int $userId): array;

    public function getActiveDealsShipsPrestige(int $userId): array;

    public function getActiveDealsBuildplansPrestige(int $userId): array;

    public function getActiveAuctions(int $userId): array;

    public function getActiveAuctionsGoods(int $userId): ?array;

    public function getActiveAuctionsShips(int $userId): array;

    public function getActiveAuctionsBuildplans(int $userId): array;

    public function getActiveAuctionsGoodsPrestige(int $userId): array;

    public function getActiveAuctionsShipsPrestige(int $userId): array;

    public function getActiveAuctionsBuildplansPrestige(int $userId): array;

    public function getAnyEndedAuctions(int $userId): array;

    public function getEndedAuctions(int $userId): array;

    public function getOwnEndedAuctions(int $userId): array;

    public function getEndedAuctionsGoods(int $userId): ?array;

    public function getEndedAuctionsShips(int $userId): array;

    public function getEndedAuctionsBuildplans(int $userId): array;

    public function getEndedAuctionsGoodsPrestige(int $userId): array;

    public function getEndedAuctionsShipsPrestige(int $userId): array;

    public function getEndedAuctionsBuildplansPrestige(int $userId): array;

    public function getOwnEndedAuctionsGoods(int $userId): ?array;

    public function getOwnEndedAuctionsShips(int $userId): array;

    public function getOwnEndedAuctionsBuildplans(int $userId): array;

    public function getOwnEndedAuctionsGoodsPrestige(int $userId): array;

    public function getOwnEndedAuctionsShipsPrestige(int $userId): array;

    public function getOwnEndedAuctionsBuildplansPrestige(int $userId): array;


    /**
     * @return TradePostInterface
     */

    public function getFergTradePost(
        int $tradePostId
    ): ?TradePostInterface;
}
