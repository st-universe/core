<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\TradeStorageInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class TradePostStorageManager implements TradePostStorageManagerInterface
{
    private StorageRepositoryInterface $storageRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private TradePostInterface $tradePost;

    private int $userId;

    private $storageSum;

    private $storage;

    public function __construct(
        StorageRepositoryInterface $storageRepository,
        CommodityRepositoryInterface $commodityRepository,
        TradePostInterface $tradePost,
        int $userId
    ) {
        $this->storageRepository = $storageRepository;
        $this->commodityRepository = $commodityRepository;
        $this->tradePost = $tradePost;
        $this->userId = $userId;
    }

    public function getTradePost(): TradePostInterface
    {
        return $this->tradePost;
    }

    public function getStorageSum(): int
    {
        if ($this->storageSum === null) {
            $this->storageSum = array_reduce(
                $this->getStorage(),
                function (int $value, TradeStorageInterface $storage): int {
                    return $value + $storage->getAmount();
                },
                0
            );
        }
        return $this->storageSum;
    }

    public function getFreeStorage(): int
    {
        return max(0, $this->tradePost->getStorage() - $this->getStorageSum());
    }

    public function getStorage(): array
    {
        if ($this->storage === null) {
            $this->storage = [];

            foreach ($this->storageRepository->getByTradePostAndUser($this->tradePost->getId(), $this->userId) as $storage) {
                $this->storage[$storage->getCommodityId()] = $storage;
            }
        }

        return $this->storage;
    }

    public function upperStorage(int $commodityId, int $amount): void
    {
        /** @var StorageInterface[] $storage */
        $storage = $this->getStorage();

        $stor = $storage[$commodityId] ?? null;
        if ($stor === null) {
            $stor = $this->storageRepository->prototype();
            $stor->setUserId($this->userId);
            $stor->setCommodity($this->commodityRepository->find($commodityId));
            $stor->setTradePost($this->tradePost);
        }
        $stor->setAmount($stor->getAmount() + $amount);

        $this->storageRepository->save($stor);
    }

    public function lowerStorage(int $commodityId, int $amount): void
    {
        $storage = $this->getStorage();

        $stor = $storage[$commodityId] ?? null;
        if ($stor === null) {
            return;
        }

        if ($stor->getAmount() <= $amount) {
            $this->storageRepository->delete($stor);
            return;
        }
        $stor->setAmount($stor->getAmount() - $amount);

        $this->storageRepository->save($stor);
    }
}
