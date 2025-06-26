<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MiningQueue;

/**
 * @extends ObjectRepository<MiningQueue>
 */
interface MiningQueueRepositoryInterface extends ObjectRepository
{
    public function prototype(): MiningQueue;

    public function getByShip(int $shipId): ?MiningQueue;

    public function save(MiningQueue $miningqueue): void;

    public function delete(MiningQueue $miningqueue): void;

    public function truncateByShipId(int $shipId): void;
}
