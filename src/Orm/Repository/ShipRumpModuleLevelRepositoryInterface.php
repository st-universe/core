<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftRump;

/**
 * @extends ObjectRepository<ShipRumpModuleLevel>
 * 
 * @method ShipRumpModuleLevel[] findAll()
 */
interface ShipRumpModuleLevelRepositoryInterface extends ObjectRepository
{
    public function save(ShipRumpModuleLevel $entity): void;

    public function getByShipRump(SpacecraftRump $rump): ?ShipRumpModuleLevel;
}
