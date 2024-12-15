<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<SpacecraftRump>
 *
 * @method null|SpacecraftRumpInterface find(integer $id)
 */
interface SpacecraftRumpRepositoryInterface extends ObjectRepository
{
    public function save(SpacecraftRumpInterface $obj): void;

    /**
     * @return array<array{rump_id: int, amount: int, name: string}>
     */
    public function getGroupedInfoByUser(UserInterface $user): array;

    /**
     * @return array<SpacecraftRumpInterface>
     */
    public function getBuildableByUserAndBuildingFunction(int $userId, BuildingFunctionEnum $buildingFunction): array;

    /**
     * @return array<int, SpacecraftRumpInterface>
     */
    public function getBuildableByUser(int $userId): array;

    /**
     * @return array<SpacecraftRumpInterface>
     */
    public function getWithoutDatabaseEntry(): array;

    /**
     * @return array<SpacecraftRumpInterface>
     */
    public function getStartableByColony(int $colonyId): array;

    /**
     * @return iterable<SpacecraftRumpInterface>
     */
    public function getList(): iterable;
}
