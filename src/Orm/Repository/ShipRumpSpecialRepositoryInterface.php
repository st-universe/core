<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\ShipRumpSpecialInterface;

/**
 * @extends ObjectRepository<ShipRumpSpecial>
 */
interface ShipRumpSpecialRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ShipRumpSpecialInterface>
     */
    public function getByShipRump(int $rumpId): array;
}
