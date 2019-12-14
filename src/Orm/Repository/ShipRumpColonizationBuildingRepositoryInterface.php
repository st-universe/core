<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpColonizationBuildingInterface;
use Stu\Orm\Entity\ShipRumpInterface;

interface ShipRumpColonizationBuildingRepositoryInterface extends ObjectRepository
{
    public function findByShipRump(ShipRumpInterface $shipRump): ?ShipRumpColonizationBuildingInterface;
}
