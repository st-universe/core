<?php

namespace Stu\Orm\Entity;

interface BuildingCostInterface
{
    public function getId(): int;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): BuildingCostInterface;

    public function getGoodId(): int;

    public function setGoodId(int $goodId): BuildingCostInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): BuildingCostInterface;

    public function getGood(): CommodityInterface;

    public function getHalfAmount(): int;
}