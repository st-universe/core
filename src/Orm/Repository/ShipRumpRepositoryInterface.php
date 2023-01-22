<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\ShipRumpInterface;

/**
 * @extends ObjectRepository<ShipRump>
 *
 * @method null|ShipRumpInterface find(integer $id)
 */
interface ShipRumpRepositoryInterface extends ObjectRepository
{
    public function getGroupedInfoByUser(int $userId): array;

    /**
     * @return ShipRumpInterface[]
     */
    public function getBuildableByUserAndBuildingFunction(int $userId, int $buildingFunction): array;

    /**
     * @return ShipRumpInterface[]
     */
    public function getBuildableByUser(int $userId): array;

    /**
     * @return ShipRumpInterface[]
     */
    public function getWithoutDatabaseEntry(): array;

    /**
     * @return ShipRumpInterface[]
     */
    public function getStartableByUserAndColony(int $userId, int $colonyId): array;

    /**
     * @return ShipRumpInterface[]
     */
    public function getList(): iterable;
}
