<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\SpacecraftEmergency;
use Stu\Orm\Entity\SpacecraftEmergencyInterface;

/**
 * @extends ObjectRepository<SpacecraftEmergency>
 */
interface SpacecraftEmergencyRepositoryInterface extends ObjectRepository
{
    public function prototype(): SpacecraftEmergencyInterface;

    public function save(SpacecraftEmergencyInterface $spacecraftEmergency): void;

    public function getByShipId(int $shipId): ?SpacecraftEmergencyInterface;

    /**
     * @return SpacecraftEmergencyInterface[]
     */
    public function getActive(): array;
}
