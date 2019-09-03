<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\ModuleBuildingFunctionInterface;

interface ModuleBuildingFunctionRepositoryInterface
{
    /**
     * @return ModuleBuildingFunctionInterface[]
     */
    public function getByBuildingFunctionAndUser(int $buildingFunction, int $userId): array;
}