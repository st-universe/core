<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\SpacecraftBuildplan;

/**
 * @extends ObjectRepository<SpacecraftBuildplan>
 *
 * @method null|SpacecraftBuildplan find(integer $id)
 * @method SpacecraftBuildplan[] findAll()
 */
interface SpacecraftBuildplanRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<SpacecraftBuildplan>
     */
    public function getByUserAndBuildingFunction(int $userId, BuildingFunctionEnum $buildingFunction): array;

    public function getCountByRumpAndUser(int $rumpId, int $userId): int;

    public function getByUserShipRumpAndSignature(int $userId, int $rumpId, string $signature): ?SpacecraftBuildplan;

    public function getShuttleBuildplan(int $commodityId): ?SpacecraftBuildplan;

    /**
     * @return array<SpacecraftBuildplan>
     */
    public function getStationBuildplansByUser(int $userId): array;

    public function getStationBuildplanByRump(int $rumpId): ?SpacecraftBuildplan;

    /**
     * @return array<SpacecraftBuildplan>
     */
    public function getShipyardBuildplansByUser(int $userId): array;

    public function prototype(): SpacecraftBuildplan;

    public function save(SpacecraftBuildplan $spacecraftBuildplan): void;

    public function delete(SpacecraftBuildplan $spacecraftBuildplan): void;

    /** @return array<SpacecraftBuildplan> */
    public function getByUser(int $userId): array;

    public function findByUserAndName(int $userId, string $name): ?SpacecraftBuildplan;

    /** @return array<SpacecraftBuildplan> */
    public function getAllNonNpcBuildplans(): array;

    public function truncateAllBuildplansExceptNoOne(): void;

    /**
     * @return SpacecraftBuildplan[]
     */
    public function getByUserAndRump(int $userId, int $rumpId): array;
}
