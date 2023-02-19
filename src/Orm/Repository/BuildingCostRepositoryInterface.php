<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingCost;
use Stu\Orm\Entity\BuildingCostInterface;

/**
 * @extends ObjectRepository<BuildingCost>
 */
interface BuildingCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<BuildingCostInterface>
     */
    public function getByBuilding(int $buildingId): array;
}
