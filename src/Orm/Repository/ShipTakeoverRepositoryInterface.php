<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\ShipTakeoverInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<ShipTakeover>
 */
interface ShipTakeoverRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipTakeoverInterface;

    public function save(ShipTakeoverInterface $shipTakeover): void;

    public function delete(ShipTakeoverInterface $shipTakeover): void;

    /** @return array<ShipTakeoverInterface> */
    public function getByTargetOwner(UserInterface $user): array;
}
