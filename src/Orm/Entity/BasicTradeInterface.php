<?php

namespace Stu\Orm\Entity;

interface BasicTradeInterface
{
    public function getId(): int;

    public function setFaction(FactionInterface $faction): BasicTradeInterface;

    public function getFaction(): FactionInterface;

    public function setCommodity(CommodityInterface $commodity): BasicTradeInterface;

    public function getCommodity(): CommodityInterface;

    public function setValue(int $value): BasicTradeInterface;

    public function getValue(): int;

    public function setDate(int $date): BasicTradeInterface;
}
