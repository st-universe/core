<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BuildingFieldAlternativeInterface;

final class BuildingFieldAlternativeRepository extends EntityRepository implements BuildingFieldAlternativeRepositoryInterface
{
    public function getByBuildingAndFieldType(int $buildingId, int $fieldType): ?BuildingFieldAlternativeInterface
    {
        return $this->findOneBy([
            'buildings_id' => $buildingId,
            'fieldtype' => $fieldType
        ]);
    }

    public function getByBuildingId(int $buildingId): array
    {
        return $this->findBy([
            'buildings_id' => $buildingId,
        ]);
    }
}