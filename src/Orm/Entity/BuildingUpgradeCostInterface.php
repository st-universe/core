<?php

namespace Stu\Orm\Entity;

interface BuildingUpgradeCostInterface
{
    public function getId(): int;

    public function setBuildingUpgradeId(int $building_upgrade_id): BuildingUpgradeCostInterface;

    public function getBuildingUpgradeId(): int;

    public function setGoodId(int $good_id): BuildingUpgradeCostInterface;

    public function getGoodId(): int;

    public function setAmount(int $amount): BuildingUpgradeCostInterface;

    public function getAmount(): int;

    public function getGood(): CommodityInterface;

    public function getUpgrade(): BuildingUpgradeInterface;
}
