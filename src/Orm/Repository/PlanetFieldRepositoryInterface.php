<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;

/**
 * @extends ObjectRepository<PlanetField>
 *
 * @method null|PlanetField find(integer $id)
 */
interface PlanetFieldRepositoryInterface extends ObjectRepository
{
    public function prototype(): PlanetField;

    public function save(PlanetField $planetField): void;

    public function delete(PlanetField $planetField): void;

    public function getByColonyAndFieldId(int $colonyId, int $fieldId): ?PlanetField;

    public function getByColonyAndFieldIndex(int $colonyId, int $fieldIndex): ?PlanetField;

    /**
     * @return array<PlanetField>
     */
    public function getByColonyAndType(int $colonyId, int $planetFieldTypeId): array;

    /**
     * @param array<int> $state
     * @param array<int> $excludedFields
     *
     * @return array<PlanetField>
     */
    public function getEnergyConsumingByHost(
        PlanetFieldHostInterface $host,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = []
    ): array;

    /**
     * @return array<PlanetField>
     */
    public function getEnergyProducingByHost(PlanetFieldHostInterface $host): array;

    /**
     * @param array<int> $state
     * @param array<int> $excludedFields
     *
     * @return array<PlanetField>
     */
    public function getCommodityConsumingByHostAndCommodity(
        PlanetFieldHostInterface $host,
        int $commodityId,
        array $state = [0, 1],
        ?int $limit = null,
        array $excludedFields = []
    ): array;

    /**
     * @return array<PlanetField>
     */
    public function getCommodityProducingByHostAndCommodity(PlanetFieldHostInterface $host, int $commodityId): array;

    /**
     * @return array<PlanetField>
     */
    public function getHousingProvidingByHost(PlanetFieldHostInterface $host): array;

    /**
     * @return array<PlanetField>
     */
    public function getWorkerConsumingByHost(PlanetFieldHostInterface $host): array;

    /**
     * @param array<int> $excludedFields
     *
     * @return array<PlanetField>
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
     * @param array<int> $ignoredFieldIds
     */
    public function getCountByColonyAndBuildingFunctionAndState(
        PlanetFieldHostInterface $host,
        array $buildingFunctions,
        array $state,
        array $ignoredFieldIds = []
    ): int;

    /**
     * @param array<int> $buildingFunctionIds
     *
     * @return array<PlanetField>
     */
    public function getByColonyAndBuildingFunction(
        int $colonyId,
        array $buildingFunctionIds
    ): array;

    public function getMaxShieldsOfHost(PlanetFieldHostInterface $host): int;

    /**
     * @return array<PlanetField>
     */
    public function getInConstructionByUser(int $userId): array;

    /**
     * @return array<PlanetField>
     */
    public function getByConstructionFinish(int $finishTime): array;

    /**
     * @return array<PlanetField>
     */
    public function getByColonyWithBuilding(PlanetFieldHostInterface $host): array;

    /**
     * @param array<int> $excludedFields
     */
    public function getEnergyProductionByHost(PlanetFieldHostInterface $host, array $excludedFields = [-1]): int;

    public function truncateByColony(Colony $colony): void;
}
