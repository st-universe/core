<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeTransferRepositoryInterface;
use TradePostData;

final class TradeLibFactory implements TradeLibFactoryInterface
{
    private $tradeLicenseRepository;

    private $tradeTransferRepository;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeTransferRepositoryInterface $tradeTransferRepository
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeTransferRepository = $tradeTransferRepository;
    }

    public function createTradeAccountTal(
        TradePostData $tradePost,
        int $userId
    ): TradeAccountTalInterface {
        return new TradeAccountTal(
            $this->tradeLicenseRepository,
            $this->tradeTransferRepository,
            $tradePost,
            $userId
        );
    }

    public function createTradePostStorageManager(
        TradePostData $tradePost,
        int $userId
    ): TradePostStorageManagerInterface {
        return new TradePostStorageManager(
            $tradePost,
            $userId
        );
    }
}