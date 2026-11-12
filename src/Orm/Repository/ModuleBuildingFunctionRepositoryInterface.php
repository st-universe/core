<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\ModuleBuildingFunction;

/**
 * @extends ObjectRepository<ModuleBuildingFunction>
 */
interface ModuleBuildingFunctionRepositoryInterface extends ObjectRepository
{
    public function save(ModuleBuildingFunction $function): void;

    /**
     * @return array<ModuleBuildingFunction>
     */
    public function getByBuildingFunctionAndUser(BuildingFunctionEnum $buildingFunction, int $userId): array;
}
