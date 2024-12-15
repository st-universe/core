<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpCost;
use Stu\Orm\Entity\ShipRumpCostInterface;

/**
 * @extends ObjectRepository<ShipRumpCost>
 */
interface ShipRumpCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ShipRumpCostInterface>
     */
    public function getByShipRump(int $rumpId): array;
}
