<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BuildingCost;

/**
 * @extends EntityRepository<BuildingCost>
 */
final class BuildingCostRepository extends EntityRepository implements BuildingCostRepositoryInterface
{
    #[Override]
    public function getByBuilding(int $buildingId): array
    {
        return $this->findBy([
            'buildings_id' => $buildingId
        ]);
    }
}
