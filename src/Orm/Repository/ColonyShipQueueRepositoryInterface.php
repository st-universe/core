<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyShipQueue;

/**
 * @extends ObjectRepository<ColonyShipQueue>
 */
interface ColonyShipQueueRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyShipQueue;

    public function save(ColonyShipQueue $post): void;

    public function delete(ColonyShipQueue $post): void;

    public function stopQueueByColonyAndBuildingFunction(int $colonyId, BuildingFunctionEnum $buildingFunction): void;

    public function restartQueueByColonyAndBuildingFunction(int $colonyId, BuildingFunctionEnum $buildingFunction): void;

    public function getAmountByColonyAndBuildingFunctionAndMode(int $colonyId, BuildingFunctionEnum $buildingFunction, int $mode): int;

    /**
     * @return array<ColonyShipQueue>
     */
    public function getByColony(int $colonyId): array;

    /**
     * @return array<ColonyShipQueue>
     */
    public function getByColonyAndMode(int $colonyId, int $mode): array;

    /**
     * @return array<ColonyShipQueue>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<ColonyShipQueue>
     */
    public function getByUserAndMode(int $userId, int $mode): array;

    public function getCountByBuildplan(int $buildplanId): int;

    /**
     * @return array<ColonyShipQueue>
     */
    public function getFinishedJobs(): array;

    public function truncateByColony(Colony $colony): void;

    public function truncateByColonyAndBuildingFunction(Colony $colony, BuildingFunctionEnum $buildingFunction): void;

    public function truncateByShip(int $shipId): void;
}
