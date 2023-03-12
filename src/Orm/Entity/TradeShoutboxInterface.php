<?php

namespace Stu\Orm\Entity;

interface TradeShoutboxInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getTradeNetworkId(): int;

    public function setTradeNetworkId(int $tradeNetworkId): TradeShoutboxInterface;

    public function getDate(): int;

    public function setDate(int $date): TradeShoutboxInterface;

    public function getMessage(): string;

    public function setMessage(string $message): TradeShoutboxInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): TradeShoutboxInterface;
}