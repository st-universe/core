<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingUpgradeCost;
use Stu\Orm\Entity\BuildingUpgradeCostInterface;

/**
 * @extends ObjectRepository<BuildingUpgradeCost>
 */
interface BuildingUpgradeCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<BuildingUpgradeCostInterface>
     */
    public function getByBuildingUpgradeId(int $buildingUpgradeId): array;
}
