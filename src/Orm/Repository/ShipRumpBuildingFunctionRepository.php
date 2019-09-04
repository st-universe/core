<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

final class ShipRumpBuildingFunctionRepository extends EntityRepository implements ShipRumpBuildingFunctionRepositoryInterface
{
    public function getByShipRump(int $shipRumpid): array
    {
        return $this->findBy([
            'rump_id' => $shipRumpid
        ]);
    }
}