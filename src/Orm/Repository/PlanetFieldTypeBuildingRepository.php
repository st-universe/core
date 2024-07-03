<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;

/**
 * @extends EntityRepository<PlanetFieldTypeBuilding>
 */
final class PlanetFieldTypeBuildingRepository extends EntityRepository implements PlanetFieldTypeBuildingRepositoryInterface
{
    #[Override]
    public function getByBuilding(int $buildingId): array
    {
        return $this->findBy([
            'buildings_id' => $buildingId
        ]);
    }
}
