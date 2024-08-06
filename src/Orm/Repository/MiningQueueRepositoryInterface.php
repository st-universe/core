<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MiningQueue;
use Stu\Orm\Entity\MiningQueueInterface;

/**
 * @extends ObjectRepository<MiningQueue>
 */
interface MiningQueueRepositoryInterface extends ObjectRepository
{
    public function prototype(): MiningQueueInterface;

    public function getByShip(int $shipId): ?MiningQueueInterface;

    public function save(MiningqueueInterface $miningqueue): void;

    public function delete(MiningQueueInterface $miningqueue): void;

    public function truncateByShipId(int $shipId): void;
}