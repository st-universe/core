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
     * @return BuildingCommodityInterface[]
     */
    public function getByBuilding(int $buildingId): array;

    /**
     * @return iterable<array{commodity_id: int, gc: int, pc: int}>
     */
    public function getProductionByColony(int $colonyId, int $colonyClassId): iterable;

    public function getProductionByCommodityAndUser(int $commodityId, UserInterface $user): int;
}
