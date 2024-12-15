<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipRumpColonizationBuilding;
use Stu\Orm\Entity\ShipRumpColonizationBuildingInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;

/**
 * @extends EntityRepository<ShipRumpColonizationBuilding>
 */
final class ShipRumpColonizationBuildingRepository extends EntityRepository implements ShipRumpColonizationBuildingRepositoryInterface
{
    #[Override]
    public function findByShipRump(SpacecraftRumpInterface $shipRump): ?ShipRumpColonizationBuildingInterface
    {
        return $this->findOneBy([
            'rump_id' => $shipRump
        ]);
    }
}
