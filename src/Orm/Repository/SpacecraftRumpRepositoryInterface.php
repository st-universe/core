<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<SpacecraftRump>
 *
 * @method null|SpacecraftRump find(integer $id)
 */
interface SpacecraftRumpRepositoryInterface extends ObjectRepository
{
    public function save(SpacecraftRump $obj): void;

    /**
     * @return array<array{rump_id: int, amount: int, name: string}>
     */
    public function getGroupedInfoByUser(User $user): array;

    /**
     * @return array<SpacecraftRump>
     */
    public function getBuildableByUserAndBuildingFunction(int $userId, BuildingFunctionEnum $buildingFunction): array;

    /**
     * @return array<int, SpacecraftRump>
     */
    public function getBuildableByUser(int $userId): array;

    /**
     * @return array<SpacecraftRump>
     */
    public function getWithoutDatabaseEntry(): array;

    /**
     * @return array<SpacecraftRump>
     */
    public function getStartableByColony(int $colonyId): array;

    /**
     * @return iterable<SpacecraftRump>
     */
    public function getList(): iterable;
}
