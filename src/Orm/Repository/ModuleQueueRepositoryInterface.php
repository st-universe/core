<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleQueue;
use Stu\Orm\Entity\ModuleQueueInterface;

/**
 * @extends ObjectRepository<ModuleQueue>
 *
 * @method ModuleQueueInterface[] findAll()
 */
interface ModuleQueueRepositoryInterface extends ObjectRepository
{
    /**
     * @return ModuleQueueInterface[]
     */
    public function getByUser(int $userId): array;

    /**
     * @return ModuleQueueInterface[]
     */
    public function getByColony(int $colonyId): array;

    public function getByColonyAndModuleAndBuilding(
        int $colonyId,
        int $moduleId,
        int $buildingFunction
    ): ?ModuleQueueInterface;

    public function getByColonyAndBuilding(
        int $colonyId,
        array $buildingFunctions
    ): array;

    public function prototype(): ModuleQueueInterface;

    public function save(ModuleQueueInterface $moduleQueue): void;

    public function delete(ModuleQueueInterface $moduleQueue): void;

    public function getAmountByColonyAndModule(int $colonyId, int $moduleId): int;
}
