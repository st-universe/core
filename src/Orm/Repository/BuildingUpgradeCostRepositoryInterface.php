<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingUpgradeCost;

/**
 * @extends ObjectRepository<BuildingUpgradeCost>
 */
interface BuildingUpgradeCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<BuildingUpgradeCost>
     */
    public function getByBuildingUpgradeId(int $buildingUpgradeId): array;
}
