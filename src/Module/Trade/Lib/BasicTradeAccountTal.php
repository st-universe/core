<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Override;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\BasicTradeInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StorageInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class BasicTradeAccountTal implements BasicTradeAccountTalInterface
{
    /**
     * @var array<StorageInterface>|null
     */
    private ?array $storage = null;

    /**
     * @param array<BasicTradeInterface> $basicTrades
     */
    public function __construct(private StorageRepositoryInterface $storageRepository, private TradePostInterface $tradePost, private array $basicTrades, private int $userId, private CommodityRepositoryInterface $commodityRepository)
    {
    }

    #[Override]
    public function getId(): int
    {
        return $this->tradePost->getId();
    }

    #[Override]
    public function getShip(): ShipInterface
    {
        return $this->tradePost->getShip();
    }

    #[Override]
    public function getTradePostDescription(): string
    {
        return $this->tradePost->getDescription();
    }

    #[Override]
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

    #[Override]
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

    #[Override]
    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage(),
            fn (int $value, StorageInterface $storage): int => $value + $storage->getAmount(),
            0
        );
    }

    #[Override]
    public function isOverStorage(): bool
    {
        return $this->getStorageSum() > $this->tradePost->getStorage();
    }

    #[Override]
    public function getStorageCapacity(): int
    {
        return $this->tradePost->getStorage();
    }
}
