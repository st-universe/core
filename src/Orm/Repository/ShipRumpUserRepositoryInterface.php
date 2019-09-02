<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpUserInterface;

interface ShipRumpUserRepositoryInterface extends ObjectRepository
{
    public function isAvailableForUser(int $shipRumpId, int $userId): bool;

    public function prototype(): ShipRumpUserInterface;

    public function save(ShipRumpUserInterface $shipRumpUser): void;
}