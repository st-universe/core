<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingGoodInterface;

interface BuildingGoodRepositoryInterface extends ObjectRepository
{
    /**
     * @return BuildingGoodInterface[]
     */
    public function getByBuilding(int $buildingId): array;

    public function getProductionByColony(int $colonyId, int $planetTypeId): iterable;

    public function getProductionByCommodityAndUser(int $commodityId, int $userId): int;
}
