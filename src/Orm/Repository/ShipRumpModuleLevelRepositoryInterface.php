<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;

interface ShipRumpModuleLevelRepositoryInterface extends ObjectRepository
{
    public function getByShipRump(int $shipRumpId): ?ShipRumpModuleLevelInterface;
}