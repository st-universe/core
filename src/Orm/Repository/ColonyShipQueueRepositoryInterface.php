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

    public function getAmountByColonyAndBuildingFunctionAndMode(int $colonyId, int $buildingFunctionId, int $mode): int;

    /**
     * @return array<ColonyShipQueueInterface>
     */
    public function getByColony(int $colonyId): array;

    /**
     * @return array<ColonyShipQueueInterface>
     */
    public function getByColonyAndMode(int $colonyId, int $mode): array;

    /**
     * @return array<ColonyShipQueueInterface>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<ColonyShipQueueInterface>
     */
    public function getByUserAndMode(int $userId, int $mode): array;

    public function getCountByBuildplan(int $buildplanId): int;

    /**
     * @return array<ColonyShipQueueInterface>
     */
    public function getFinishedJobs(): array;

    public function truncateByColony(ColonyInterface $colony): void;

    public function truncateByColonyAndBuildingFunction(ColonyInterface $colony, int $buildingFunctionId): void;

    public function truncateByShip(int $shipId): void;
}