<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\ModuleBuildingFunction;
use Stu\Orm\Entity\ModuleBuildingFunctionInterface;

/**
 * @extends ObjectRepository<ModuleBuildingFunction>
 */
interface ModuleBuildingFunctionRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<ModuleBuildingFunctionInterface>
     */
    public function getByBuildingFunctionAndUser(BuildingFunctionEnum $buildingFunction, int $userId): array;
}
