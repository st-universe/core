<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpColonizationBuilding;
use Stu\Orm\Entity\ShipRumpColonizationBuildingInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;

/**
 * @extends ObjectRepository<ShipRumpColonizationBuilding>
 */
interface ShipRumpColonizationBuildingRepositoryInterface extends ObjectRepository
{
    public function findByShipRump(SpacecraftRumpInterface $shipRump): ?ShipRumpColonizationBuildingInterface;
}
