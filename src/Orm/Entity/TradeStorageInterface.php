<?php

namespace Stu\Orm\Entity;

interface TradeStorageInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): TradeStorageInterface;

    public function getTradePostId(): int;

    public function setTradePostId(int $tradePostId): TradeStorageInterface;

    public function getGoodId(): int;

    public function setGoodId(int $commodityId): TradeStorageInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): TradeStorageInterface;

    public function getTradePost(): TradePostInterface;

    public function setTradePost(TradePostInterface $tradePost): TradeStorageInterface;

    public function getGood(): CommodityInterface;

    public function setGood(CommodityInterface $commodity): TradeStorageInterface;
}