<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Entity\ShipBuildplanInterface;

/**
 * @extends ObjectRepository<ShipBuildplan>
 *
 * @method null|ShipBuildplanInterface find(integer $id)
 * @method ShipBuildplanInterface[] findAll()
 */
interface ShipBuildplanRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<ShipBuildplanInterface>
     */
    public function getByUserAndBuildingFunction(int $userId, int $buildingFunction): array;

    public function getCountByRumpAndUser(int $rumpId, int $userId): int;

    public function getByUserShipRumpAndSignature(int $userId, int $shipRumpId, string $signature): ?ShipBuildplanInterface;

    public function getShuttleBuildplan(int $commodityId): ?ShipBuildplanInterface;

    /**
     * @return array<ShipBuildplanInterface>
     */
    public function getStationBuildplansByUser(int $userId): array;

    /**
     * @return array<ShipBuildplanInterface>
     */
    public function getShipyardBuildplansByUser(int $userId): array;

    public function prototype(): ShipBuildplanInterface;

    public function save(ShipBuildplanInterface $shipBuildplan): void;

    public function delete(ShipBuildplanInterface $shipBuildplan): void;

    /**
     * @return array<ShipBuildplanInterface>
     */
    public function getByUser(int $userId): array;

    public function findByUserAndName(int $userId, string $name): ?ShipBuildplanInterface;

    public function truncateAllBuildplansExceptNoOne(): void;

    /**
     * @return ShipBuildplanInterface[]
     */
    public function getByUserAndRump(int $userId, int $rumpId): array;
}
