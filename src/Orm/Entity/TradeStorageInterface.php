<?php

namespace Stu\Orm\Entity;

interface TradeStorageInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): TradeStorageInterface;

    public function getTradePostId(): int;

    public function setTradePostId(int $tradePostId): TradeStorageInterface;

    public function getGoodId(): int;

    public function setGoodId(int $commodityId): TradeStorageInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): TradeStorageInterface;

    public function getTradePost(): TradePostInterface;

    public function setTradePost(TradePostInterface $tradePost): TradeStorageInterface;

    /**
     * @deprecated
     */
    public function getGood(): CommodityInterface;

    /**
     * @deprecated
     */
    public function setGood(CommodityInterface $commodity): TradeStorageInterface;

    public function getCommodity(): CommodityInterface;

    public function setCommodity(CommodityInterface $commodity): TradeStorageInterface;
}
