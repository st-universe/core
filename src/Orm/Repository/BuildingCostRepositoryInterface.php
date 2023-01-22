<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingCost;

/**
 * @extends ObjectRepository<BuildingCost>
 */
interface BuildingCostRepositoryInterface extends ObjectRepository
{
    public function getByBuilding(int $buildingId): array;
}
