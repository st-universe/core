<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpColonizationBuildingInterface;

final class ShipRumpColonizationBuildingRepository extends EntityRepository implements ShipRumpColonizationBuildingRepositoryInterface
{
    public function findByShipRump(int $shipRumpId): ?ShipRumpColonizationBuildingInterface
    {
        return $this->findOneBy([
            'rump_id' => $shipRumpId
        ]);
    }
}