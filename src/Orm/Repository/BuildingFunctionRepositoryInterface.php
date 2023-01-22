<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\BuildingFunctionInterface;

/**
 * @extends ObjectRepository<BuildingFunction>
 *
 * @method null|BuildingFunctionInterface find(integer $id)
 */
interface BuildingFunctionRepositoryInterface extends ObjectRepository
{
    /**
     * @return BuildingFunctionInterface[]
     */
    public function getByBuilding(int $buildingId): array;
}
