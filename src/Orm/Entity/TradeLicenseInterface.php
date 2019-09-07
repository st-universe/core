<?php

namespace Stu\Orm\Entity;

use User;

interface TradeLicenseInterface
{
    public function getId(): int;

    public function getTradePostId(): int;

    public function setTradePostId(int $tradePostId): TradeLicenseInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): TradeLicenseInterface;

    public function getDate(): int;

    public function setDate(int $date): TradeLicenseInterface;

    public function getUser(): User;

    public function getTradePost(): TradePostInterface;

    public function setTradePost(TradePostInterface $tradePost): TradeLicenseInterface;
}