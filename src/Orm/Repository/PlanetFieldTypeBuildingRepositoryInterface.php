<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;

/**
 * @extends ObjectRepository<PlanetFieldTypeBuilding>
 */
interface PlanetFieldTypeBuildingRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<PlanetFieldTypeBuilding>
     */
    public function getByBuilding(int $buildingId): array;
}
