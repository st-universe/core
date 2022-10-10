<?php

namespace Stu\Orm\Entity;

interface BuildingUpgradeCostInterface
{
    public function getId(): int;

    public function setBuildingUpgradeId(int $building_upgrade_id): BuildingUpgradeCostInterface;

    public function getBuildingUpgradeId(): int;

    public function setCommodityId(int $commodityId): BuildingUpgradeCostInterface;

    public function getCommodityId(): int;

    public function setAmount(int $amount): BuildingUpgradeCostInterface;

    public function getAmount(): int;

    public function getCommodity(): CommodityInterface;

    public function getUpgrade(): BuildingUpgradeInterface;
}
