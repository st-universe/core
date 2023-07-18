<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;

final class TradeLibFactory implements TradeLibFactoryInterface
{
    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeTransferRepositoryInterface $tradeTransferRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private StorageRepositoryInterface $storageRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeTransferRepositoryInterface $tradeTransferRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        StorageRepositoryInterface $storageRepository,
        CommodityRepositoryInterface $commodityRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeTransferRepository = $tradeTransferRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->storageRepository = $storageRepository;
        $this->commodityRepository = $commodityRepository;
        $this->loggerUtilFactory = $loggerUtilFactory;
    }

    public function createTradeAccountTal(
        TradePostInterface $tradePost,
        int $userId
    ): TradeAccountTalInterface {
        return new TradeAccountTal(
            $this->tradeLicenseRepository,
            $this->tradeTransferRepository,
            $this->tradeOfferRepository,
            $this->storageRepository,
            $tradePost,
            $userId
        );
    }

    public function createDealAccountTal(
        TradePostInterface $tradePost,
        array $deals,
        int $userId
    ): DealAccountTalInterface {
        return new DealAccountTal(
            $this->storageRepository,
            $tradePost,
            $deals,
            $userId,
            $this->commodityRepository,
            $this->loggerUtilFactory
        );
    }

    public function createBasicTradeAccountTal(
        TradePostInterface $tradePost,
        array $basicTrades,
        int $userId
    ): BasicTradeAccountTalInterface {
        $filteredBasicTrades = array_filter(
            $basicTrades,
            fn(BasicTradeInterface $basicTrade): bool => $basicTrade->getFaction()->getId() === $tradePost->getTradeNetwork()
        );

        return new BasicTradeAccountTal(
            $this->storageRepository,
            $tradePost,
            $filteredBasicTrades,
            $userId,
            $this->commodityRepository
        );
    }

    public function createTradePostStorageManager(
        TradePostInterface $tradePost,
        UserInterface $user
    ): TradePostStorageManagerInterface {
        return new TradePostStorageManager(
            $this->storageRepository,
            $this->commodityRepository,
            $tradePost,
            $user
        );
    }
}
