<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;

/**
 * @extends ObjectRepository<ShipRumpModuleLevel>
 * 
 * @method ShipRumpModuleLevelInterface[] findAll()
 */
interface ShipRumpModuleLevelRepositoryInterface extends ObjectRepository
{
    public function save(ShipRumpModuleLevelInterface $entity): void;

    public function getByShipRump(SpacecraftRumpInterface $rump): ?ShipRumpModuleLevelInterface;
}
