<?php

namespace Stu\Orm\Entity;

interface TradeTransactionInterface
{
    public function getId(): int;

    public function getWantedCommodityId(): int;

    public function setWantedCommodityId(int $wantedCommodityId): TradeTransactionInterface;

    public function getWantedCommodityCount(): int;

    public function setWantedCommodityCount(int $wantedCommodityCount): TradeTransactionInterface;

    public function getOfferedCommodityId(): int;

    public function setOfferedCommodityId(int $offeredCommodityId): TradeTransactionInterface;

    public function getOfferedCommodityCount(): int;

    public function setOfferedCommodityCount(int $offeredCommodityCount): TradeTransactionInterface;

    public function getDate(): int;

    public function setDate(int $date): TradeTransactionInterface;

    public function getTradePostId(): int;

    public function setTradePostId(int $tradepost_id): TradeTransactionInterface;

    public function getWantedCommodity(): CommodityInterface;

    public function setWantedCommodity(CommodityInterface $wantedCommodity): TradeTransactionInterface;

    public function getOfferedCommodity(): CommodityInterface;

    public function setOfferedCommodity(CommodityInterface $offeredCommodity): TradeTransactionInterface;
}
