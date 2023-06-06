<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\BuildingCommodityInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<BuildingCommodity>
 */
interface BuildingCommodityRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<BuildingCommodityInterface>
     */
    public function getByBuilding(int $buildingId): array;

    /**
     * @return iterable<array{commodity_id: int, gc: int, pc: int}>
     */
    public function getProductionByColony(int $colonyId, int $colonyClassId): iterable;


    /**
     * @return iterable<array{commodity_id: int, gc: int, pc: int}>
     */
    public function getProductionByColonyWithoutEffects(int $colonyId, int $colonyClassId): iterable;

    /**
     * Returns the production sum of default commodities of all colonies for the given user
     *
     * @return iterable<array{commodity_id: int, commodity_name: string, gc: int}> Production data, ordered by commodity
     */
    public function getProductionSumForAllUserColonies(UserInterface $user): iterable;

    public function getProductionByCommodityAndUser(int $commodityId, UserInterface $user): int;
}
