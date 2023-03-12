<?php

namespace Stu\Orm\Entity;

interface BuildingCommodityInterface
{
    public function getId(): int;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): BuildingCommodityInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): BuildingCommodityInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): BuildingCommodityInterface;

    public function getCommodity(): CommodityInterface;
}
