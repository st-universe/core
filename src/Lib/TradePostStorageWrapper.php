<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\Orm\Repository\TradePostRepositoryInterface;
use TradeOffer;
use TradeStorage;

class TradePostStorageWrapper
{

    function __construct($postId, $userId)
    {
        $this->tradePost = $postId;
        $this->userId = $userId;
    }

    private $tradePost = null;
    private $userId = null;
    private $storage = array();

    public function addStorageEntry($stor)
    {
        $this->storage[$stor->getGoodId()] = $stor;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getTradePostId()
    {
        return $this->tradePost;
    }

    public function getTradePost()
    {
        // @todo refactor
        global $container;

        return $container->get(TradePostRepositoryInterface::class)->find((int) $this->getTradePostId());
    }

    private $storageSum = null;

    public function getStorageSum()
    {
        if ($this->storageSum === null) {
            $sum = 0;
            $sum += TradeStorage::getStorageSumBy($this->getTradePostId(), $this->getUserId());
            $sum += TradeOffer::getOfferSumBy($this->getTradePostId(), $this->getUserId());
            $this->storageSum = $sum;
        }
        return $this->storageSum;
    }

    public function upperSum($count)
    {
        $this->storageSum = $this->getStorageSum() + $count;
    }

    public function lowerSum($count)
    {
        $this->storageSum = $this->getStorageSum() - $count;
    }
}