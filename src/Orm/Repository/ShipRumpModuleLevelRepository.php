<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;

final class ShipRumpModuleLevelRepository extends EntityRepository implements ShipRumpModuleLevelRepositoryInterface
{
    public function getByShipRump(int $shipRumpId): ?ShipRumpModuleLevelInterface
    {
        return $this->findOneBy([
            'rump_id' => $shipRumpId,
        ]);
    }
}