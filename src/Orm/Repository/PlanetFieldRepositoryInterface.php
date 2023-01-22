<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
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

    /**
     * @return PlanetFieldInterface[]
     */
    public function getByColonyAndType(int $colonyId, int $planetFieldTypeId): iterable;

    /**
     * @return PlanetFieldInterface[]
     */
    public function getEnergyConsumingByColony(
        int $colonyId,
        array $state = [0, 1],
        ?int $limit = null
    ): iterable;

    /**
     * @return PlanetFieldInterface[]
     */
    public function getEnergyProducingByColony(int $colonyId): iterable;

    /**
     * @return PlanetFieldInterface[]
     */
    public function getCommodityConsumingByColonyAndCommodity(
        int $colonyId,
        int $commodityId,
        array $state = [0, 1],
        ?int $limit = null
    ): iterable;

    /**
     * @return PlanetFieldInterface[]
     */
    public function getCommodityProducingByColonyAndCommodity(int $colonyId, int $commodityId): iterable;

    /**
     * @return PlanetFieldInterface[]
     */
    public function getHousingProvidingByColony(int $colonyId): iterable;

    /**
     * @return PlanetFieldInterface[]
     */
    public function getWorkerConsumingByColony(int $colonyId): iterable;

    public function getCountByColonyAndBuilding(int $colonyId, int $buildingId): int;

    public function getCountByBuildingAndUser(int $buildingId, int $userId): int;

    public function getCountByColonyAndBuildingFunctionAndState(
        int $colonyId,
        array $buildingFunctionIds,
        array $state
    ): int;

    public function getByColonyAndBuildingFunction(
        int $colonyId,
        array $buildingFunctionIds
    ): array;

    public function getMaxShieldsOfColony(int $colonyId): int;

    /**
     * @return PlanetFieldInterface[]
     */
    public function getInConstructionByUser(int $userId): iterable;

    /**
     * @return PlanetFieldInterface[]
     */
    public function getByConstructionFinish(int $finishTime): iterable;

    /**
     * @return PlanetFieldInterface[]
     */
    public function getByColonyWithBuilding(int $colonyId): iterable;

    public function getEnergyProductionByColony(int $colonyId): int;

    public function truncateByColony(ColonyInterface $colony): void;
}
