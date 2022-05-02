<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\TradeStorageInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;

final class BasicTradeAccountTal implements BasicTradeAccountTalInterface
{
    private TradeStorageRepositoryInterface $tradeStorageRepository;

    private TradePostInterface $tradePost;

    private array $basicTrades;

    private int $userId;

    private $storage;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        TradeStorageRepositoryInterface $tradeStorageRepository,
        TradePostInterface $tradePost,
        array $basicTrades,
        int $userId,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->tradeStorageRepository = $tradeStorageRepository;
        $this->tradePost = $tradePost;
        $this->basicTrades = $basicTrades;
        $this->userId = $userId;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function getId(): int
    {
        return $this->tradePost->getId();
    }

    public function getShip(): ShipInterface
    {
        return $this->tradePost->getShip();
    }

    public function getTradePostDescription(): string
    {
        return $this->tradePost->getDescription();
    }

    public function getBasicTradeItems(): array
    {
        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $result = [];

        $storage = $this->getStorage();

        foreach ($this->basicTrades as $basicTrade) {
            $commodityId = $basicTrade->getCommodity()->getId();
            $result[] = new BasicTradeItem($basicTrade, $storage[$commodityId]);

            $this->loggerUtil->log(sprintf(
                'basicTradeCID: %d, storageCID: %d, storageCN: %s',
                $basicTrade->getCommodity()->getId(),
                $storage[$commodityId] !== null ? $storage[$commodityId]->getGood()->getId() : 0,
                $storage[$commodityId] !== null ? $storage[$commodityId]->getGood()->getName() : 'null'
            ));
        }

        return $result;
    }

    public function getLatinumItem(): BasicTradeItem
    {
        $latinumStorage = $this->getStorage()[CommodityTypeEnum::GOOD_LATINUM];
        return new BasicTradeItem(null, $latinumStorage);
    }

    private function getStorage(): array
    {
        if ($this->storage === null) {
            $this->storage = $this->tradeStorageRepository->getByTradePostAndUser(
                $this->tradePost->getId(),
                $this->userId
            );
        }
        return $this->storage;
    }

    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage(),
            function (int $value, TradeStorageInterface $storage): int {
                return $value + $storage->getAmount();
            },
            0
        );
    }

    public function isOverStorage(): bool
    {
        return $this->getStorageSum() > $this->tradePost->getStorage();
    }

    public function getStorageCapacity(): int
    {
        return $this->tradePost->getStorage();
    }
}
