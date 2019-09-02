<?php

namespace Stu\Orm\Entity;

interface TradeTransferInterface
{
    public function getId(): int;

    public function getTradePostId(): int;

    public function setTradePostId(int $tradePostId): TradeTransferInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): TradeTransferInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): TradeTransferInterface;

    public function getDate(): int;

    public function setDate(int $date): TradeTransferInterface;
}