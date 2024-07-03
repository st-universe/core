<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpInterface;

/**
 * @extends EntityRepository<ShipRumpBuildingFunction>
 */
final class ShipRumpBuildingFunctionRepository extends EntityRepository implements ShipRumpBuildingFunctionRepositoryInterface
{
    #[Override]
    public function getByShipRump(ShipRumpInterface $shipRump): array
    {
        return $this->findBy([
            'rump_id' => $shipRump
        ]);
    }
}
