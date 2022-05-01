<?php

namespace Stu\Orm\Entity;

interface BasicTradeInterface
{
    public function getId(): int;

    public function setFaction(FactionInterface $faction): BasicTradeInterface;

    public function getFaction(): FactionInterface;

    public function setCommodity(CommodityInterface $commodity): BasicTradeInterface;

    public function getCommodity(): CommodityInterface;

    public function setBuySell(int $buySell): BasicTradeInterface;

    public function getBuySell(): int;

    public function setValue(int $value): BasicTradeInterface;

    public function getValue(): int;

    public function setDate(int $date): BasicTradeInterface;

    public function getDate(): int;

    public function setUniqId(string $uniqid): BasicTradeInterface;

    public function getUniqId(): string;

    public function setUserId(int $userId): BasicTradeInterface;
}
