<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpColonizationBuilding;
use Stu\Orm\Entity\SpacecraftRump;

/**
 * @extends ObjectRepository<ShipRumpColonizationBuilding>
 */
interface ShipRumpColonizationBuildingRepositoryInterface extends ObjectRepository
{
    public function findByShipRump(SpacecraftRump $shipRump): ?ShipRumpColonizationBuilding;
}
