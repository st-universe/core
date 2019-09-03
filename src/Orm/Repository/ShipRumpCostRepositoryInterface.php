<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpCostInterface;

interface ShipRumpCostRepositoryInterface extends ObjectRepository
{
    /**
     * @return ShipRumpCostInterface[]
     */
    public function getByShipRump(int $shipRumpId): array;
}