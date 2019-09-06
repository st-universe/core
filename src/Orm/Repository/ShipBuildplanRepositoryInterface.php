<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipBuildplanInterface;

interface ShipBuildplanRepositoryInterface extends ObjectRepository
{
    /**
     * @return ShipBuildplanInterface[]
     */
    public function getByUserAndBuildingFunction(int $userId, int $buildingFunction): array;

    public function getCountByRumpAndUser(int $rumpId, int $userId): int;

    public function getByUserAndSignature(int $userId, string $signature): ?ShipBuildplanInterface;

    public function prototype(): ShipBuildplanInterface;

    public function save(ShipBuildplanInterface $shipBuildplan): void;

    public function delete(ShipBuildplanInterface $shipBuildplan): void;

    /**
     * @return ShipBuildplanInterface[]
     */
    public function getByUser(int $userId): array;
}