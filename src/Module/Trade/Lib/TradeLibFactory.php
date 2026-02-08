<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;

final class TradeLibFactory implements TradeLibFactoryInterface
{
    public function __construct(
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private TradeTransferRepositoryInterface $tradeTransferRepository,
        private TradeOfferRepositoryInterface $tradeOfferRepository,
        private StorageRepositoryInterface $storageRepository,
        private CommodityRepositoryInterface $commodityRepository
    ) {}

    #[\Override]
    public function createTradeAccountWrapper(
        TradePost $tradePost,
        int $userId
    ): TradeAccountWrapperInterface {
        return new TradeAccountWrapper(
            $this->tradeLicenseRepository,
            $this->tradeTransferRepository,
            $this->tradeOfferRepository,
            $this->storageRepository,
            $tradePost,
            $userId
        );
    }

    #[\Override]
    public function createBasicTradeAccountWrapper(
        TradePost $tradePost,
        array $basicTrades,
        int $userId
    ): BasicTradeAccountWrapperInterface {
        $filteredBasicTrades = array_filter(
            $basicTrades,
            fn (BasicTrade $basicTrade): bool => $basicTrade->getFaction()->getId() === $tradePost->getTradeNetwork()
        );

        return new BasicTradeAccountWrapper(
            $this->storageRepository,
            $tradePost,
            $filteredBasicTrades,
            $userId,
            $this->commodityRepository
        );
    }

    #[\Override]
    public function createTradePostStorageManager(
        TradePost $tradePost,
        User $user
    ): TradePostStorageManagerInterface {
        return new TradePostStorageManager(
            $this->storageRepository,
            $this->commodityRepository,
            $tradePost,
            $user
        );
    }
}
