<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\ColonyShipRepairInterface;

/**
 * @extends ObjectRepository<ColonyShipRepair>
 */
interface ColonyShipRepairRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyShipRepairInterface;

    /**
     * @return ColonyShipRepairInterface[]
     */
    public function getByColonyField(int $colonyId, int $fieldId): array;

    public function getByShip(int $shipId): ?ColonyShipRepairInterface;

    /**
     * @return ColonyShipRepairInterface[]
     */
    public function getMostRecentJobs(int $tickId): array;

    public function save(ColonyShipRepairInterface $colonyShipRepair): void;

    public function delete(ColonyShipRepairInterface $colonyShipRepair): void;

    public function truncateByShipId(int $shipId): void;
}
