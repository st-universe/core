<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;

/**
 * @extends ObjectRepository<ShipRumpModuleLevel>
 */
interface ShipRumpModuleLevelRepositoryInterface extends ObjectRepository
{
    public function getByShipRump(int $rumpId): ?ShipRumpModuleLevelInterface;
}
