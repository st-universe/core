<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

final class ShipRumpCostRepository extends EntityRepository implements ShipRumpCostRepositoryInterface
{
    public function getByShipRump(int $shipRumpId): array
    {
        return $this->findBy([
            'rump_id' => $shipRumpId
        ]);
    }
}