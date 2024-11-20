<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipSystem;
use Stu\Orm\Entity\ShipSystemInterface;

/**
 * @extends ObjectRepository<ShipSystem>
 */
interface ShipSystemRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipSystemInterface;

    public function save(ShipSystemInterface $post): void;

    public function delete(ShipSystemInterface $post): void;

    /**
     * @return array<ShipSystemInterface>
     */
    public function getByShip(int $shipId): array;

    /**
     * @return array<ShipSystemInterface>
     */
    public function getTrackingShipSystems(int $targetId): array;

    public function getByShipAndModule(int $shipId, int $moduleId): ?ShipSystemInterface;

    /**
     * @return array<ShipSystemInterface>
     */
    public function getWebConstructingShipSystems(int $webId): array;

    public function truncateByShip(int $shipId): void;
}