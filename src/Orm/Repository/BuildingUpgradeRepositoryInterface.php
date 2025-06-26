<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingUpgrade;

/**
 * @extends ObjectRepository<BuildingUpgrade>
 *
 * @method null|BuildingUpgrade find($id)
 */
interface BuildingUpgradeRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<BuildingUpgrade>
     */
    public function getByBuilding(int $buildingId, int $userId): array;
}
