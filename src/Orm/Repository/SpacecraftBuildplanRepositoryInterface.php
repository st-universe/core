<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;

/**
 * @extends ObjectRepository<SpacecraftBuildplan>
 *
 * @method null|SpacecraftBuildplanInterface find(integer $id)
 * @method SpacecraftBuildplanInterface[] findAll()
 */
interface SpacecraftBuildplanRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<SpacecraftBuildplanInterface>
     */
    public function getByUserAndBuildingFunction(int $userId, BuildingFunctionEnum $buildingFunction): array;

    public function getCountByRumpAndUser(int $rumpId, int $userId): int;

    public function getByUserShipRumpAndSignature(int $userId, int $rumpId, string $signature): ?SpacecraftBuildplanInterface;

    public function getShuttleBuildplan(int $commodityId): ?SpacecraftBuildplanInterface;

    /**
     * @return array<SpacecraftBuildplanInterface>
     */
    public function getStationBuildplansByUser(int $userId): array;

    public function getStationBuildplanByRump(int $rumpId): ?SpacecraftBuildplanInterface;

    /**
     * @return array<SpacecraftBuildplanInterface>
     */
    public function getShipyardBuildplansByUser(int $userId): array;

    public function prototype(): SpacecraftBuildplanInterface;

    public function save(SpacecraftBuildplanInterface $spacecraftBuildplan): void;

    public function delete(SpacecraftBuildplanInterface $spacecraftBuildplan): void;

    /** @return array<SpacecraftBuildplanInterface> */
    public function getByUser(int $userId): array;

    public function findByUserAndName(int $userId, string $name): ?SpacecraftBuildplanInterface;

    /** @return array<SpacecraftBuildplanInterface> */
    public function getAllNonNpcBuildplans(): array;

    public function truncateAllBuildplansExceptNoOne(): void;

    /**
     * @return SpacecraftBuildplanInterface[]
     */
    public function getByUserAndRump(int $userId, int $rumpId): array;
}