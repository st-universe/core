<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;

/**
 * @method null|RepairTaskInterface find(integer $id)
 */
interface RepairTaskRepositoryInterface extends ObjectRepository
{
    public function prototype(): RepairTaskInterface;

    public function save(RepairTaskInterface $obj): void;

    public function delete(RepairTaskInterface $post): void;

    public function getByShip(int $shipId): ShipInterface;

    public function truncateByShipId(int $shipId): void;
}
