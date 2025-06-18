<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\ShipTakeoverInterface;

/**
 * @extends ObjectRepository<ShipTakeover>
 */
interface ShipTakeoverRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipTakeoverInterface;

    public function save(ShipTakeoverInterface $shipTakeover): void;

    public function delete(ShipTakeoverInterface $shipTakeover): void;
}
