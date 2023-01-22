<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;
use Stu\Orm\Entity\PlanetFieldTypeBuildingInterface;

/**
 * @extends ObjectRepository<PlanetFieldTypeBuilding>
 */
interface PlanetFieldTypeBuildingRepositoryInterface extends ObjectRepository
{
    /**
     * @return PlanetFieldTypeBuildingInterface[]
     */
    public function getByBuilding(int $buildingId): array;
}
