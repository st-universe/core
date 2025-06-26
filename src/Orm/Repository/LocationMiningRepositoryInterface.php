<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LocationMining;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\MiningQueue;

/**
 * @extends ObjectRepository<LocationMining>
 *
 * @method null|LocationMining find(integer $id)
 */
interface LocationMiningRepositoryInterface extends ObjectRepository
{
    public function prototype(): LocationMining;

    public function save(LocationMining $locationMining): void;

    /**
     * @return LocationMining[]
     */
    public function getMiningAtLocation(Ship $ship): array;

    public function getMiningQueueAtLocation(Ship $ship): ?MiningQueue;

    public function findById(int $id): ?LocationMining;

    /**
     * @return LocationMining[]
     */
    public function findDepletedEntries(): array;
}
