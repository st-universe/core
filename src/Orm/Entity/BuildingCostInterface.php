<?php

namespace Stu\Orm\Entity;

interface BuildingCostInterface
{
    public function getId(): int;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): BuildingCostInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): BuildingCostInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): BuildingCostInterface;

    public function getCommodity(): CommodityInterface;

    public function getHalfAmount(): int;
}
