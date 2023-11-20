<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingUpgrade;
use Stu\Orm\Entity\BuildingUpgradeInterface;

/**
 * @extends ObjectRepository<BuildingUpgrade>
 * 
 * @method null|BuildingUpgradeInterface find($id)
 */
interface BuildingUpgradeRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<BuildingUpgradeInterface>
     */
    public function getByBuilding(int $buildingId, int $userId): array;
}
