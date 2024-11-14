<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<ShipRump>
 *
 * @method null|ShipRumpInterface find(integer $id)
 */
interface ShipRumpRepositoryInterface extends ObjectRepository
{
    public function save(ShipRumpInterface $obj): void;

    /**
     * @return array<array{rump_id: int, amount: int, name: string}>
     */
    public function getGroupedInfoByUser(UserInterface $user): array;

    /**
     * @return array<ShipRumpInterface>
     */
    public function getBuildableByUserAndBuildingFunction(int $userId, BuildingFunctionEnum $buildingFunction): array;

    /**
     * @return array<int, ShipRumpInterface>
     */
    public function getBuildableByUser(int $userId): array;

    /**
     * @return array<ShipRumpInterface>
     */
    public function getWithoutDatabaseEntry(): array;

    /**
     * @return array<ShipRumpInterface>
     */
    public function getStartableByColony(int $colonyId): array;

    /**
     * @return iterable<ShipRumpInterface>
     */
    public function getList(): iterable;
}
