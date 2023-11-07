<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\RepairTaskInterface;
use Stu\Orm\Entity\ShipInterface;

/**
 * @extends ObjectRepository<RepairTask>
 *
 * @method null|RepairTaskInterface find(integer $id)
 */
interface RepairTaskRepositoryInterface extends ObjectRepository
{
    public function prototype(): RepairTaskInterface;

    public function save(RepairTaskInterface $obj): void;

    public function delete(RepairTaskInterface $post): void;

    public function getByShip(int $shipId): ?ShipInterface;

    public function truncateByShipId(int $shipId): void;

    /**
     * @return array<RepairTaskInterface>
     */
    public function getFinishedRepairTasks(): array;
}
