<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpColonizationBuildingInterface;

interface ShipRumpColonizationBuildingRepositoryInterface extends ObjectRepository
{
    public function findByShipRump(int $shipRumpId): ?ShipRumpColonizationBuildingInterface;
}