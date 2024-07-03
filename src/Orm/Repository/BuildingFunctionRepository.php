<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BuildingFunction;

/**
 * @extends EntityRepository<BuildingFunction>
 */
final class BuildingFunctionRepository extends EntityRepository implements BuildingFunctionRepositoryInterface
{
    #[Override]
    public function getByBuilding(int $buildingId): array
    {
        return $this->findBy([
            'buildings_id' => $buildingId
        ]);
    }
}
