<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleQueue;

/**
 * @extends ObjectRepository<ModuleQueue>
 *
 * @method ModuleQueue[] findAll()
 */
interface ModuleQueueRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ModuleQueue>
     */
    public function getByUser(int $userId): array;

    /**
     * @return list<ModuleQueue>
     */
    public function getByColony(int $colonyId): array;

    public function getByColonyAndModuleAndBuilding(
        int $colonyId,
        int $moduleId,
        int $buildingFunction
    ): ?ModuleQueue;

    /**
     * @param array<int> $buildingFunctions
     *
     * @return array<ModuleQueue>
     */
    public function getByColonyAndBuilding(
        int $colonyId,
        array $buildingFunctions
    ): array;

    public function prototype(): ModuleQueue;

    public function save(ModuleQueue $moduleQueue): void;

    public function delete(ModuleQueue $moduleQueue): void;

    public function getAmountByColonyAndModule(int $colonyId, int $moduleId): int;
}
