<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingFieldAlternative;
use Stu\Orm\Entity\BuildingFieldAlternativeInterface;

/**
 * @extends ObjectRepository<BuildingFieldAlternative>
 */
interface BuildingFieldAlternativeRepositoryInterface extends ObjectRepository
{
    public function getByBuildingAndFieldType(int $buildingId, int $fieldType): ?BuildingFieldAlternativeInterface;

    /**
     * @return list<BuildingFieldAlternativeInterface>
     */
    public function getByBuildingIdAndResearchedByUser(int $buildingId, int $userId): iterable;
}
