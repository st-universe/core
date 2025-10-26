<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class BasicTradeAccountWrapper implements BasicTradeAccountWrapperInterface
{
    /**
     * @var array<Storage>|null
     */
    private ?array $storage = null;

    /**
     * @param array<BasicTrade> $basicTrades
     */
    public function __construct(private StorageRepositoryInterface $storageRepository, private TradePost $tradePost, private array $basicTrades, private int $userId, private CommodityRepositoryInterface $commodityRepository) {}

    #[\Override]
    public function getId(): int
    {
        return $this->tradePost->getId();
    }

    #[\Override]
    public function getStation(): Station
    {
        return $this->tradePost->getStation();
    }

    #[\Override]
    public function getTradePostDescription(): string
    {
        return $this->tradePost->getDescription();
    }

    #[\Override]
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

    #[\Override]
    public function getLatinumItem(): BasicTradeItem
    {
        $latinumStorage = $this->getStorage()[CommodityTypeConstants::COMMODITY_LATINUM] ?? null;
        $latinumCommodity = $this->commodityRepository->find(CommodityTypeConstants::COMMODITY_LATINUM);
        return new BasicTradeItem(null, $latinumStorage, $latinumCommodity);
    }

    /**
     * @return array<Storage>
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

    #[\Override]
    public function getStorageSum(): int
    {
        return array_reduce(
            $this->getStorage(),
            fn(int $value, Storage $storage): int => $value + $storage->getAmount(),
            0
        );
    }

    #[\Override]
    public function isOverStorage(): bool
    {
        return $this->getStorageSum() > $this->tradePost->getStorage();
    }

    #[\Override]
    public function getStorageCapacity(): int
    {
        return $this->tradePost->getStorage();
    }
}
