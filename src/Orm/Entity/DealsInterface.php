<?php

namespace Stu\Orm\Entity;

interface DealsInterface
{
    public function getId(): int;

    public function setFaction(FactionInterface $faction): DealsInterface;

    public function getFaction(): FactionInterface;

    public function setAuction(bool $auction): DealsInterface;

    public function getAuction(): bool;

    public function getAmount(): int;

    public function setAmount(DealsInterface $amount): DealsInterface;

    public function getgiveCommodityId(): int;

    public function setgiveCommodityId(CommodityInterface $givecommodity): DealsInterface;

    public function getwantCommodityId(): int;

    public function setwantCommodityId(CommodityInterface $wantcommodity): DealsInterface;

    public function getgiveCommodityAmount(): CommodityInterface;

    public function setgiveCommodityAmount(CommodityInterface $givecommodityamount): DealsInterface;

    public function getwantCommodityAmount(): CommodityInterface;

    public function setwantCommodityAmount(CommodityInterface $wantcommodityamount): DealsInterface;

    public function getwantPrestige(): int;

    public function setwantPrestige(int $wantprestige): DealsInterface;

    public function getBuildplanId(): int;

    public function setBuildplanId(int $buildplanid): DealsInterface;

    public function getShip(): bool;

    public function setShip(bool $ship): DealsInterface;

    public function getTime(): int;

    public function setTime(int $time): DealsInterface;

    public function getEnd(): int;

    public function setEnd(int $end): DealsInterface;

    public function getWantedCommodity(): CommodityInterface;

    public function setWantedCommodity(CommodityInterface $wantedCommodity): DealsInterface;

    public function getGiveCommodity(): CommodityInterface;

    public function setGiveCommodity(CommodityInterface $giveCommodity): DealsInterface;
}