<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Lib;

use TradePostData;
use TradeStorage;
use TradeStorageData;

final class TradePostStorageManager implements TradePostStorageManagerInterface
{
    private $tradePost;

    private $userId;

    private $storageSum;

    public function __construct(
        TradePostData $tradePost,
        int $userId
    ) {
        $this->tradePost = $tradePost;
        $this->userId = $userId;
    }

    public function getTradePost(): TradePostData
    {
        return $this->tradePost;
    }

    public function getStorageSum(): int
    {
        if ($this->storageSum === null) {
            $this->storageSum = (int) TradeStorage::getStorageByTradepostUser($this->tradePost->getId(), $this->userId)->getStorageSum();
        }
        return $this->storageSum;
    }

    public function getFreeStorage(): int
    {
        return max(0, $this->tradePost->getStorage() - $this->getStorageSum());
    }

    public function getStorage(): array
    {
        return TradeStorage::getStorageByTradepostUser($this->tradePost->getId(), $this->userId)->getStorage();
    }

    public function upperStorage(int $commodityId, int $amount): void
    {
        $storage = TradeStorage::getStorageByTradepostUser($this->tradePost->getId(), $this->userId);

        $stor = $storage->getStorage()[$commodityId] ?? null;
        if ($stor === null) {
            $stor = new TradeStorageData();
            $stor->setUserId($this->userId);
            $stor->setGoodId($commodityId);
            $stor->setTradePostId($this->tradePost->getId());
        }
        $stor->upperCount($amount);
        $stor->save();

        $storage->addStorageEntry($stor);
        $storage->upperSum($amount);
    }

    public function lowerStorage(int $commodityId, int $amount): void
    {
        $storage = TradeStorage::getStorageByTradepostUser($this->tradePost->getId(), $this->userId);

        /** @var TradeStorageData $stor */
        $stor = $storage->getStorage()[$commodityId] ?? null;
        if ($stor === null) {
            return;
        }

        if ($stor->getAmount() <= $amount) {
            $storage->lowerSum($stor->getAmount());
            $stor->deleteFromDatabase();
            return;
        }
        $stor->lowerCount($amount);
        $stor->save();

        $storage->lowerSum($amount);
    }
}