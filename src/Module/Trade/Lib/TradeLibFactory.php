<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeCreateLicenceRepository;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TradeLibFactory implements TradeLibFactoryInterface
{
    private TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeTransferRepositoryInterface $tradeTransferRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private UserRepositoryInterface $userRepository;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    public function __construct(
        TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeTransferRepositoryInterface $tradeTransferRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository,
        CommodityRepositoryInterface $commodityRepository,
        UserRepositoryInterface $userRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->tradeCreateLicenceRepository = $tradeCreateLicenceRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeTransferRepository = $tradeTransferRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
        $this->commodityRepository = $commodityRepository;
        $this->userRepository = $userRepository;
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
            $this->tradeStorageRepository,
            $this->tradCreateLicenceRepository,
            $tradePost,
            $userId
        );
    }

    public function createBasicTradeAccountTal(
        TradePostInterface $tradePost,
        array $basicTrades,
        int $userId
    ): BasicTradeAccountTalInterface {
        $filteredBasicTrades = array_filter(
            $basicTrades,
            function (BasicTradeInterface $basicTrade) use ($tradePost): bool {
                return $basicTrade->getFaction()->getId() === $tradePost->getTradeNetwork();
            }
        );

        return new BasicTradeAccountTal(
            $this->tradeStorageRepository,
            $tradePost,
            $filteredBasicTrades,
            $userId,
            $this->commodityRepository,
            $this->loggerUtilFactory
        );
    }

    public function createTradePostStorageManager(
        TradePostInterface $tradePost,
        int $userId
    ): TradePostStorageManagerInterface {
        return new TradePostStorageManager(
            $this->tradeStorageRepository,
            $this->commodityRepository,
            $this->userRepository,
            $tradePost,
            $userId
        );
    }
}