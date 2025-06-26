<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<ShipTakeover>
 */
interface ShipTakeoverRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipTakeover;

    public function save(ShipTakeover $shipTakeover): void;

    public function delete(ShipTakeover $shipTakeover): void;

    /** @return array<ShipTakeover> */
    public function getByTargetOwner(User $user): array;
}
