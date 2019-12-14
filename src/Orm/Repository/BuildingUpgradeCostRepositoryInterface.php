<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;

interface BuildingUpgradeCostRepositoryInterface extends ObjectRepository
{
    public function getByBuildingUpgradeId(int $buildingUpgradeId): array;
}