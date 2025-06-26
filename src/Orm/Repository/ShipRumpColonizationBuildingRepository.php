<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipRumpColonizationBuilding;
use Stu\Orm\Entity\SpacecraftRump;

/**
 * @extends EntityRepository<ShipRumpColonizationBuilding>
 */
final class ShipRumpColonizationBuildingRepository extends EntityRepository implements ShipRumpColonizationBuildingRepositoryInterface
{
    #[Override]
    public function findByShipRump(SpacecraftRump $shipRump): ?ShipRumpColonizationBuilding
    {
        return $this->findOneBy([
            'rump_id' => $shipRump
        ]);
    }
}
