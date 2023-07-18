<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class BasicTradeAccountTal implements BasicTradeAccountTalInterface
{
    private StorageRepositoryInterface $storageRepository;

    private TradePostInterface $tradePost;

    /**
     * @var array<BasicTradeInterface>
     */
    private array $basicTrades;

    private int $userId;

    /**
     * @var array<StorageInterface>|null
     */
    private ?array $storage = null;

    private CommodityRepositoryInterface $commodityRepository;

    /**
     * @param array<BasicTradeInterface> $basicTrades
     */
    public function __construct(
        StorageRepositoryInterface $storageRepository,
        TradePostInterface $tradePost,
        array $basicTrades,
        int $userId,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->storageRepository = $storageRepository;
        $this->tradePost = $tradePost;
        $this->basicTrades = $basicTrades;
        $this->userId = $userId;
        $this->commodityRepository = $commodityRepository;
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
        $result = [];

        $storage = $this->getStorage();

        foreach ($this->basicTrades as $basicTrade) {
            $commodityId = $basicTrade->getCommodity()->getId();
            $result[] = new BasicTradeItem($basicTrade, $storage[$commodityId] ?? null);
        }

        return $result;
    }

    public function getLatinumItem(): BasicTradeItem
    {
        $latinumStorage = $this->getStorage()[CommodityTypeEnum::COMMODITY_LATINUM] ?? null;
        $latinumCommodity = $this->commodityRepository->find(CommodityTypeEnum::COMMODITY_LATINUM);
        return new BasicTradeItem(null, $latinumStorage, $latinumCommodity);
    }

    /**
     * @return array<StorageInterface>
     */
    private function getStorage(): array
    {
        if ($this->storage === null) {
            $this->storage = $this->storageRepository->getByTradePostAndUser(
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
            fn(int $value, StorageInterface $storage): int => $value + $storage->getAmount(),
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
