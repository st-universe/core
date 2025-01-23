<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpBuildingFunctionInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;

/**
 * @extends ObjectRepository<ShipRumpBuildingFunction>
 */
interface ShipRumpBuildingFunctionRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ShipRumpBuildingFunctionInterface>
     */
    public function getByShipRump(SpacecraftRumpInterface $shipRump): array;
}
