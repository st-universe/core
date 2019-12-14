<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipRumpSpecialInterface;

interface ShipRumpSpecialRepositoryInterface extends ObjectRepository
{
    /**
     * @return ShipRumpSpecialInterface[]
     */
    public function getByShipRump(int $shipRumpId): array;
}