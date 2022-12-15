<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingFieldAlternativeInterface;

interface BuildingFieldAlternativeRepositoryInterface extends ObjectRepository
{
    public function getByBuildingAndFieldType(int $buildingId, int $fieldType): ?BuildingFieldAlternativeInterface;

    public function getByBuildingIdAndResearchedByUser(int $buildingId, int $userId): array;
}