<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\RepairTask;

/**
 * @extends ObjectRepository<RepairTask>
 *
 * @method null|RepairTask find(integer $id)
 */
interface RepairTaskRepositoryInterface extends ObjectRepository
{
    public function prototype(): RepairTask;

    public function save(RepairTask $obj): void;

    public function delete(RepairTask $post): void;

    public function getByShip(int $shipId): ?RepairTask;

    public function truncateByShipId(int $shipId): void;

    /**
     * @return array<RepairTask>
     */
    public function getFinishedRepairTasks(): array;
}
