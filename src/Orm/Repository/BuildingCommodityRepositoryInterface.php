<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\BuildingCommodityInterface;
use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<BuildingCommodity>
 */
interface BuildingCommodityRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<BuildingCommodityInterface>
     */
    public function getByBuilding(int $buildingId): array;

    /**
     * @return iterable<array{commodity_id: int, production: int, pc: int}>
     */
    public function getProductionByColony(PlanetFieldHostInterface $host, ColonyClassInterface $colonyClass): iterable;

    /**
     * Returns the production sum of default commodities of all colonies for the given user
     *
     * @return iterable<array{commodity_id: int, commodity_name: string, gc: int}> Production data, ordered by commodity
     */
    public function getProductionSumForAllUserColonies(UserInterface $user): iterable;

    public function getProductionByCommodityAndUser(int $commodityId, UserInterface $user): int;
}
