<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StationShipRepair;
use Stu\Orm\Entity\StationShipRepairInterface;

/**
 * @extends ObjectRepository<StationShipRepair>
 */
interface StationShipRepairRepositoryInterface extends ObjectRepository
{
    public function prototype(): StationShipRepairInterface;

    public function getByStation(int $stationId): array;

    public function getByShip(int $shipId): StationShipRepairInterface;

    /**
     * @return StationShipRepairInterface[]
     */
    public function getMostRecentJobs(): array;

    public function save(StationShipRepairInterface $stationShipRepair): void;

    public function delete(StationShipRepairInterface $stationShipRepair): void;

    public function truncateByShipId(int $shipId): void;
}
