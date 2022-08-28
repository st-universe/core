<?php

namespace Stu\Orm\Entity;

interface TradeTransactionInterface
{
    public function getId(): int;

    public function getWantedGoodId(): int;

    public function setWantedGoodId(int $wantedCommodityId): TradeTransactionInterface;

    public function getWantedGoodCount(): int;

    public function setWantedGoodCount(int $wantedGoodCount): TradeTransactionInterface;

    public function getOfferedGoodId(): int;

    public function setOfferedGoodId(int $offeredCommodityId): TradeTransactionInterface;

    public function getOfferedGoodCount(): int;

    public function setOfferedGoodCount(int $offeredGoodCount): TradeTransactionInterface;

    public function getDate(): int;

    public function setDate(int $date): TradeTransactionInterface;

    public function getTradePostId(): int;

    public function setTradePostId(int $tradepost_id): TradeTransactionInterface;

    public function getWantedCommodity(): CommodityInterface;

    public function setWantedCommodity(CommodityInterface $wantedCommodity): TradeTransactionInterface;

    public function getOfferedCommodity(): CommodityInterface;

    public function setOfferedCommodity(CommodityInterface $offeredCommodity): TradeTransactionInterface;
}