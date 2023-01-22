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
     * @return ShipRumpCostInterface[]
     */
    public function getByShipRump(int $shipRumpId): array;
}
