<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

final class PlanetFieldTypeBuildingRepository extends EntityRepository implements PlanetFieldTypeBuildingRepositoryInterface
{
    public function getByBuilding(int $buildingId): array
    {
        return $this->findBy([
            'buildings_id' => $buildingId
        ]);
    }
}
