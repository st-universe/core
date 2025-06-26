<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipyardShipQueue;

/**
 * @extends ObjectRepository<ShipyardShipQueue>
 */
interface ShipyardShipQueueRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipyardShipQueue;

    public function save(ShipyardShipQueue $post): void;

    public function delete(ShipyardShipQueue $post): void;

    /**
     * @return array<ShipyardShipQueue>
     */
    public function getByShipyard(int $stationId): array;

    /**
     * @return array<ShipyardShipQueue>
     */
    public function getByUser(int $userId): array;

    public function getAmountByShipyard(int $stationId): int;

    public function stopQueueByShipyard(int $stationId): void;

    public function restartQueueByShipyard(int $stationId): void;

    /**
     * @return array<ShipyardShipQueue>
     */
    public function getFinishedJobs(): array;
}
