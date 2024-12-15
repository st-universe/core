<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipRumpCost;

/**
 * @extends EntityRepository<ShipRumpCost>
 */
final class ShipRumpCostRepository extends EntityRepository implements ShipRumpCostRepositoryInterface
{
    #[Override]
    public function getByShipRump(int $rumpId): array
    {
        return $this->findBy([
            'rump_id' => $rumpId
        ]);
    }
}
