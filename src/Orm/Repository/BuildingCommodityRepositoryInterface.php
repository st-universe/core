<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<BuildingCommodity>
 */
interface BuildingCommodityRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<BuildingCommodity>
     */
    public function getByBuilding(int $buildingId): array;

    /**
     * @return iterable<array{commodity_id: int, production: int, pc: int}>
     */
    public function getProductionByColony(PlanetFieldHostInterface $host, ColonyClass $colonyClass): iterable;

    /**
     * Returns the production sum of default commodities of all colonies for the given user
     *
     * @return iterable<array{commodity_id: int, commodity_name: string, gc: int}> Production data, ordered by commodity
     */
    public function getProductionSumForAllUserColonies(User $user): iterable;

    public function getProductionByCommodityAndUser(int $commodityId, User $user): int;

    public function canProduceCommodity(int $userId, int $commodityId): bool;
}
