<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpCost;

/**
 * @extends EntityRepository<ShipRumpCost>
 */
final class ShipRumpCostRepository extends EntityRepository implements ShipRumpCostRepositoryInterface
{
    #[Override]
    public function getByShipRump(int $shipRumpId): array
    {
        return $this->findBy([
            'rump_id' => $shipRumpId
        ]);
    }
}
