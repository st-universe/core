<?php

namespace Stu\Orm\Entity;

interface TradeOfferInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getTradePostId(): int;

    public function setTradePostId(int $tradePostId): TradeOfferInterface;

    public function getOfferCount(): int;

    public function setOfferCount(int $offerCount): TradeOfferInterface;

    public function getWantedCommodityId(): int;

    public function setWantedCommodityId(int $wantedCommodityId): TradeOfferInterface;

    public function getWantedCommodityCount(): int;

    public function setWantedCommodityCount(int $wantedCommodityCount): TradeOfferInterface;

    public function getOfferedCommodityId(): int;

    public function setOfferedCommodityId(int $offeredCommodityId): TradeOfferInterface;

    public function getOfferedCommodityCount(): int;

    public function setOfferedCommodityCount(int $offeredCommodityCount): TradeOfferInterface;

    public function getDate(): int;

    public function setDate(int $date): TradeOfferInterface;

    public function getTradePost(): TradePostInterface;

    public function setTradePost(TradePostInterface $tradePost): TradeOfferInterface;

    public function getWantedCommodity(): CommodityInterface;

    public function setWantedCommodity(CommodityInterface $wantedCommodity): TradeOfferInterface;

    public function getOfferedCommodity(): CommodityInterface;

    public function setOfferedCommodity(CommodityInterface $offeredCommodity): TradeOfferInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): TradeOfferInterface;

    public function getStorage(): StorageInterface;
}
