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

    public function getWantedGoodId(): int;

    public function setWantedGoodId(int $wantedCommodityId): TradeOfferInterface;

    public function getWantedGoodCount(): int;

    public function setWantedGoodCount(int $wantedGoodCount): TradeOfferInterface;

    public function getOfferedGoodId(): int;

    public function setOfferedGoodId(int $offeredCommodityId): TradeOfferInterface;

    public function getOfferedGoodCount(): int;

    public function setOfferedGoodCount(int $offeredGoodCount): TradeOfferInterface;

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
