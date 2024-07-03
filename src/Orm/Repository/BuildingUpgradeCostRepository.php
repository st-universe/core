<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\BuildingUpgradeCost;

/**
 * @extends EntityRepository<BuildingUpgradeCost>
 */
final class BuildingUpgradeCostRepository extends EntityRepository implements BuildingUpgradeCostRepositoryInterface
{
    #[Override]
    public function getByBuildingUpgradeId(int $buildingUpgradeId): array
    {
        return $this->findBy([
            'buildings_upgrades_id' => $buildingUpgradeId
        ]);
    }
}
