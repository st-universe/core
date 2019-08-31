<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildingFieldAlternativeInterface;

interface BuildingFieldAlternativeRepositoryInterface extends ObjectRepository
{
    public function getByBuildingAndFieldType(int $buildingId, int $fieldType): ?BuildingFieldAlternativeInterface;

    public function getByBuildingId(int $buildingId): array;
}