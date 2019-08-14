<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

interface BuildingUpgradeCostRepositoryInterface extends ObjectRepository
{
    public function getByBuildingUpgradeId(int $buildingUpgradeId): array;
}