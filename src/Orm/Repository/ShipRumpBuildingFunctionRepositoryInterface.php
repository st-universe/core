<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpBuildingFunctionInterface;
use Stu\Orm\Entity\ShipRumpInterface;

/**
 * @extends ObjectRepository<ShipRumpBuildingFunction>
 */
interface ShipRumpBuildingFunctionRepositoryInterface extends ObjectRepository
{
    /**
     * @return ShipRumpBuildingFunctionInterface[]
     */
    public function getByShipRump(ShipRumpInterface $shipRump): array;
}
