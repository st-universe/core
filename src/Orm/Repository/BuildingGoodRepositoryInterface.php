<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingGoodInterface;

interface BuildingGoodRepositoryInterface extends ObjectRepository
{
    /**
     * @return BuildingGoodInterface[]
     */
    public function getByBuilding(int $buildingId): array;

    public function getProductionByColony(int $colonyId, int $planetTypeId): iterable;
}