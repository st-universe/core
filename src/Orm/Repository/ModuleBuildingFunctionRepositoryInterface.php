<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
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
    public function getByBuildingFunctionAndUser(int $buildingFunction, int $userId): array;
}
