<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipyardShipQueue;
use Stu\Orm\Entity\ShipyardShipQueueInterface;

/**
 * @extends ObjectRepository<ShipyardShipQueue>
 */
interface ShipyardShipQueueRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipyardShipQueueInterface;

    public function save(ShipyardShipQueueInterface $post): void;

    public function delete(ShipyardShipQueueInterface $post): void;

    /**
     * @return array<ShipyardShipQueueInterface>
     */
    public function getByShipyard(int $stationId): array;

    /**
     * @return array<ShipyardShipQueueInterface>
     */
    public function getByUser(int $userId): array;

    public function getAmountByShipyard(int $shipId): int;

    public function stopQueueByShipyard(int $shipId): void;

    public function restartQueueByShipyard(int $shipId): void;

    /**
     * @return array<ShipyardShipQueueInterface>
     */
    public function getFinishedJobs(): array;
}