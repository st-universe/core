<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpBuildingFunctionInterface;
use Stu\Orm\Entity\ShipRumpInterface;

interface ShipRumpBuildingFunctionRepositoryInterface extends ObjectRepository
{
    /**
     * @return ShipRumpBuildingFunctionInterface[]
     */
    public function getByShipRump(ShipRumpInterface $shipRump): array;
}
