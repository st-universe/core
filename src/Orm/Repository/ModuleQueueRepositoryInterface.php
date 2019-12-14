<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleQueueInterface;

interface ModuleQueueRepositoryInterface extends ObjectRepository
{
    /**
     * @return ModuleQueueInterface[]
     */
    public function getByColony(int $colonyId): array;

    public function getByColonyAndModuleAndBuilding(
        int $colonyId,
        int $moduleId,
        int $buildingFunction
    ): ?ModuleQueueInterface;

    public function prototype(): ModuleQueueInterface;

    public function save(ModuleQueueInterface $moduleQueue): void;

    public function delete(ModuleQueueInterface $moduleQueue): void;

    public function getAmountByColonyAndModule(int $colonyId, int $moduleId): int;
}