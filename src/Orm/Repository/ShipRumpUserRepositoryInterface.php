<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpUser;
use Stu\Orm\Entity\ShipRumpUserInterface;

/**
 * @extends ObjectRepository<ShipRumpUser>
 */
interface ShipRumpUserRepositoryInterface extends ObjectRepository
{
    public function isAvailableForUser(int $rumpId, int $userId): bool;

    public function prototype(): ShipRumpUserInterface;

    public function save(ShipRumpUserInterface $shipRumpUser): void;
}
