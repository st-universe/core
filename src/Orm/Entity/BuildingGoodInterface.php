<?php

namespace Stu\Orm\Entity;

interface BuildingGoodInterface
{
    public function getId(): int;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): BuildingGoodInterface;

    public function getGoodId(): int;

    public function setGoodId(int $goodId): BuildingGoodInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): BuildingGoodInterface;

    public function getGood(): CommodityInterface;
}