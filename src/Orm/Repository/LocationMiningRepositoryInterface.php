<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LocationMining;
use Stu\Orm\Entity\LocationMiningInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\MiningQueueInterface;

/**
 * @extends ObjectRepository<LocationMining>
 *
 * @method null|LocationMiningInterface find(integer $id)
 */
interface LocationMiningRepositoryInterface extends ObjectRepository
{
    public function prototype(): LocationMiningInterface;

    public function save(LocationMiningInterface $locationMining): void;

    /**
     * @return LocationMiningInterface[]
     */
    public function getMiningAtLocation(ShipInterface $ship): array;

    public function getMiningQueueAtLocation(ShipInterface $ship): ?MiningQueueInterface;

    public function findById(int $id): ?LocationMiningInterface;

    /**
     * @return LocationMiningInterface[]
     */
    public function findDepletedEntries(): array;
}
