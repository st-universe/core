<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\SpacecraftRump;

/**
 * @extends ObjectRepository<ShipRumpBuildingFunction>
 */
interface ShipRumpBuildingFunctionRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ShipRumpBuildingFunction>
     */
    public function getByShipRump(SpacecraftRump $shipRump): array;
}
