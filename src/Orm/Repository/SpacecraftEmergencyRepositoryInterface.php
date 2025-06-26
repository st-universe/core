<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SpacecraftEmergency;

/**
 * @extends ObjectRepository<SpacecraftEmergency>
 */
interface SpacecraftEmergencyRepositoryInterface extends ObjectRepository
{
    public function prototype(): SpacecraftEmergency;

    public function save(SpacecraftEmergency $spacecraftEmergency): void;

    public function getByShipId(int $shipId): ?SpacecraftEmergency;

    /**
     * @return list<SpacecraftEmergency>
     */
    public function getActive(): array;
}
