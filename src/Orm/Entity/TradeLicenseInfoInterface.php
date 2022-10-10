<?php

namespace Stu\Orm\Entity;

interface TradeLicenseInfoInterface
{
    public function getId(): int;

    public function setTradepost(TradePostInterface $tradepost): TradeLicenseInfoInterface;

    public function getTradePostId(): int;

    public function setTradePostId(int $posts_id): TradeLicenseInfoInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): TradeLicenseInfoInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): TradeLicenseInfoInterface;

    public function getDate(): int;

    public function setDate(int $date): TradeLicenseInfoInterface;

    public function getDays(): int;

    public function setDays(int $days): TradeLicenseInfoInterface;
}
