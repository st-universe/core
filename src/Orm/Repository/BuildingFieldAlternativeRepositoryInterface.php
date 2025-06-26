<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingFieldAlternative;

/**
 * @extends ObjectRepository<BuildingFieldAlternative>
 */
interface BuildingFieldAlternativeRepositoryInterface extends ObjectRepository
{
    public function getByBuildingAndFieldType(int $buildingId, int $fieldType): ?BuildingFieldAlternative;

    /**
     * @return list<BuildingFieldAlternative>
     */
    public function getByBuildingIdAndResearchedByUser(int $buildingId, int $userId): iterable;
}
