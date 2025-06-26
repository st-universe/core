<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpCost;

/**
 * @extends ObjectRepository<ShipRumpCost>
 */
interface ShipRumpCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ShipRumpCost>
     */
    public function getByShipRump(int $rumpId): array;
}
