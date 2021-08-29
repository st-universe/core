<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\ShipyardShipQueueInterface;

interface ShipyardShipQueueRepositoryInterface
{
    public function prototype(): ShipyardShipQueueInterface;

    public function save(ShipyardShipQueueInterface $post): void;

    public function delete(ShipyardShipQueueInterface $post): void;

    public function getByShipyard(int $stationId): array;

    public function getByUser(int $userId): array;

    public function getAmountByShipyard(int $shipId): int;

    public function stopQueueByShipyard(int $shipId): void;

    public function restartQueueByShipyard(int $shipId): void;

    public function getFinishedJobs(): array;
}
