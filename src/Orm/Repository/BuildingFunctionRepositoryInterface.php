<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingFunction;

/**
 * @extends ObjectRepository<BuildingFunction>
 *
 * @method null|BuildingFunction find(integer $id)
 */
interface BuildingFunctionRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<BuildingFunction>
     */
    public function getByBuilding(int $buildingId): array;
}
