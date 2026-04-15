<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StationShipRepair;

/**
 * @extends ObjectRepository<StationShipRepair>
 */
interface StationShipRepairRepositoryInterface extends ObjectRepository
{
    public function prototype(): StationShipRepair;

    /**
     * @return array<StationShipRepair>
     */
    public function getByStation(int $stationId): array;

    public function getByShip(int $shipId): ?StationShipRepair;

    /**
     * @return array<StationShipRepair>
     */
    public function getMostRecentJobs(): array;

    public function save(StationShipRepair $stationShipRepair): void;

    public function delete(StationShipRepair $stationShipRepair): void;

    public function truncateByShipId(int $shipId): void;
}
