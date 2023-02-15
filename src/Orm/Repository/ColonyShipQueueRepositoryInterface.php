<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyShipQueue;
use Stu\Orm\Entity\ColonyShipQueueInterface;

/**
 * @extends ObjectRepository<ColonyShipQueue>
 */
interface ColonyShipQueueRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyShipQueueInterface;

    public function save(ColonyShipQueueInterface $post): void;

    public function delete(ColonyShipQueueInterface $post): void;

    public function stopQueueByColonyAndBuildingFunction(int $colonyId, int $buildingFunctionId): void;

    public function restartQueueByColonyAndBuildingFunction(int $colonyId, int $buildingFunctionId): void;

    public function getAmountByColonyAndBuildingFunction(int $colonyId, int $buildingFunctionId): int;

    /**
     * @return ColonyShipQueueInterface[]
     */
    public function getByColony(int $colonyId): array;

    /**
     * @return ColonyShipQueueInterface[]
     */
    public function getByUser(int $userId): array;

    public function getCountByBuildplan(int $buildplanId): int;

    /**
     * @return ColonyShipQueueInterface[]
     */
    public function getFinishedJobs(): array;

    public function truncateByColony(ColonyInterface $colony): void;

    public function truncateByColonyAndBuildingFunction(int $colonyId, int $buildingFunctionId): void;
}
