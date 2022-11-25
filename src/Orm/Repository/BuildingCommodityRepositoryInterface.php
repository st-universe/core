<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingCommodityInterface;

interface BuildingCommodityRepositoryInterface extends ObjectRepository
{
    /**
     * @return BuildingCommodityInterface[]
     */
    public function getByBuilding(int $buildingId): array;

    public function getProductionByColony(int $colonyId, int $colonyClassId): iterable;

    public function getProductionByCommodityAndUser(int $commodityId, int $userId): int;
}
