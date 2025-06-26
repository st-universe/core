<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpUser;

/**
 * @extends ObjectRepository<ShipRumpUser>
 */
interface ShipRumpUserRepositoryInterface extends ObjectRepository
{
    public function isAvailableForUser(int $rumpId, int $userId): bool;

    public function prototype(): ShipRumpUser;

    public function save(ShipRumpUser $shipRumpUser): void;
}
