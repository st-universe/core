<?php

namespace Stu\Orm\Entity;

interface BasicTradeInterface
{
    public function getId(): int;

    public function setFaction(FactionInterface $faction): BasicTradeInterface;

    public function setCommodity(CommodityInterface $commodity): BasicTradeInterface;

    public function setValue(int $value): BasicTradeInterface;

    public function setDate(int $date): BasicTradeInterface;

    public function setRandom(int $random): BasicTradeInterface;

    public function getRandom(): int;
}
