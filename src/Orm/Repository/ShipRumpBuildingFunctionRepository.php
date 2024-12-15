<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\SpacecraftRumpInterface;

/**
 * @extends EntityRepository<ShipRumpBuildingFunction>
 */
final class ShipRumpBuildingFunctionRepository extends EntityRepository implements ShipRumpBuildingFunctionRepositoryInterface
{
    #[Override]
    public function getByShipRump(SpacecraftRumpInterface $shipRump): array
    {
        return $this->findBy([
            'rump_id' => $shipRump
        ]);
    }
}
