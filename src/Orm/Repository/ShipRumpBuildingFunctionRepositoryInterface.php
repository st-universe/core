<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpBuildingFunctionInterface;

interface ShipRumpBuildingFunctionRepositoryInterface extends ObjectRepository
{
    /**
     * @return ShipRumpBuildingFunctionInterface[]
     */
    public function getByShipRump(int $shipRumpid): array;
}