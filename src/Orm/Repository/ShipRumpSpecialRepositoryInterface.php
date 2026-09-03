<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpSpecial;

/**
 * @extends ObjectRepository<ShipRumpSpecial>
 */
interface ShipRumpSpecialRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<ShipRumpSpecial>
     */
    public function getByShipRump(int $rumpId): array;
}
