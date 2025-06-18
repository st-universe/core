<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldInterface;

/**
 * @extends ObjectRepository<PlanetField>
 *
 * @method null|PlanetFieldInterface find(integer $id)
 */
interface PlanetFieldRepositoryInterface extends ObjectRepository
{
    public function prototype(): PlanetFieldInterface;

    public function save(PlanetFieldInterface $planetField): void;

    public function delete(PlanetFieldInterface $planetField): void;

    public function getByColonyAndFieldId(int $colonyId, int $fieldId): ?PlanetFieldInterface;

    public function getByColonyAndFieldIndex(int $colonyId, int $fieldIndex): ?PlanetFieldInterface;

    /**
     * @return array<PlanetFieldInterface>
     */
    public function getByColonyAndType(int $colonyId, int $planetFieldTypeId): array;

    /**
     * @param array<int> $state
     * @param array<int> $excludedFields
     *
     * @return array<PlanetFieldInterface>
     */
    public function getEnergyConsumingByHost(
        PlanetFieldHostInterface $host,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = []
    ): array;

    /**
     * @return array<PlanetFieldInterface>
     */
    public function getEnergyProducingByHost(PlanetFieldHostInterface $host): array;

    /**
     * @param array<int> $state
     * @param array<int> $excludedFields
     *
     * @return array<PlanetFieldInterface>
     */
    public function getCommodityConsumingByHostAndCommodity(
        PlanetFieldHostInterface $host,
        int $commodityId,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = []
    ): array;

    /**
     * @return array<PlanetFieldInterface>
     */
    public function getCommodityProducingByHostAndCommodity(PlanetFieldHostInterface $host, int $commodityId): array;

    /**
     * @return array<PlanetFieldInterface>
     */
    public function getHousingProvidingByHost(PlanetFieldHostInterface $host): array;

    /**
     * @return array<PlanetFieldInterface>
     */
    public function getWorkerConsumingByHost(PlanetFieldHostInterface $host): array;

    /**
     * @param array<int> $excludedFields
     *
     * @return array<PlanetFieldInterface>
     */
    public function getWorkerConsumingByColonyAndState(
        int $colonyId,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = []
    ): array;

    public function getCountByHostAndBuilding(PlanetFieldHostInterface $host, int $buildingId): int;

    public function getCountByBuildingAndUser(int $buildingId, int $userId): int;

    /**
     * @param array<BuildingFunctionEnum> $buildingFunctions
     * @param array<int> $state
     */
    public function getCountByColonyAndBuildingFunctionAndState(
        PlanetFieldHostInterface $host,
        array $buildingFunctions,
        array $state
    ): int;

    /**
     * @param array<int> $buildingFunctionIds
     *
     * @return array<PlanetFieldInterface>
     */
    public function getByColonyAndBuildingFunction(
        int $colonyId,
        array $buildingFunctionIds
    ): array;

    public function getMaxShieldsOfHost(PlanetFieldHostInterface $host): int;

    /**
     * @return array<PlanetFieldInterface>
     */
    public function getInConstructionByUser(int $userId): array;

    /**
     * @return array<PlanetFieldInterface>
     */
    public function getByConstructionFinish(int $finishTime): array;

    /**
     * @return array<PlanetFieldInterface>
     */
    public function getByColonyWithBuilding(PlanetFieldHostInterface $host): array;

    /**
     * @param array<int> $excludedFields
     */
    public function getEnergyProductionByHost(PlanetFieldHostInterface $host, array $excludedFields = [-1]): int;

    public function truncateByColony(ColonyInterface $colony): void;
}
