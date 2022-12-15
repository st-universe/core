<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\PlanetFieldTypeBuildingInterface;

interface PlanetFieldTypeBuildingRepositoryInterface
{
    /**
     * @return PlanetFieldTypeBuildingInterface[]
     */
    public function getByBuilding(int $buildingId): array;
}