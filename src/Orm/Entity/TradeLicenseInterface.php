<?php

namespace Stu\Orm\Entity;

interface TradeLicenseInterface
{
    public function getId(): int;

    public function getTradePostId(): int;

    public function setTradePostId(int $tradePostId): TradeLicenseInterface;

    public function getUserId(): int;

    public function getDate(): int;

    public function setDate(int $date): TradeLicenseInterface;

    public function getExpired(): int;

    public function setExpired(int $expired): TradeLicenseInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): TradeLicenseInterface;

    public function getTradePost(): TradePostInterface;

    public function setTradePost(TradePostInterface $tradePost): TradeLicenseInterface;
}