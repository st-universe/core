<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyShipRepair;

/**
 * @extends ObjectRepository<ColonyShipRepair>
 */
interface ColonyShipRepairRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyShipRepair;

    /**
     * @return array<ColonyShipRepair>
     */
    public function getByColonyField(int $colonyId, int $fieldId): array;

    public function getByShip(int $shipId): ?ColonyShipRepair;

    /**
     * @return array<ColonyShipRepair>
     */
    public function getMostRecentJobs(int $tickId): array;

    public function save(ColonyShipRepair $colonyShipRepair): void;

    public function delete(ColonyShipRepair $colonyShipRepair): void;

    public function truncateByShipId(int $shipId): void;
}
