<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
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
    /**
     * @return array<array{rump_id: int, amount: int, name: string}>
     */
    public function getGroupedInfoByUser(UserInterface $user): array;

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
    public function getStartableByColony(int $colonyId): array;

    /**
     * @return ShipRumpInterface[]
     */
    public function getList(): iterable;
}