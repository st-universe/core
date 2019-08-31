<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\BuildingGoodInterface;

interface BuildingGoodRepositoryInterface
{
    /**
     * @return BuildingGoodInterface[]
     */
    public function getByBuilding(int $buildingId): array;
}