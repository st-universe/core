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
     * @return list<ShipSystemInterface>
     */
    public function getByShip(int $shipId): array;

    /**
     * @return list<ShipSystemInterface>
     */
    public function getTrackingShipSystems(int $targetId): array;

    /**
     * @return list<ShipSystemInterface>
     */
    public function getWebConstructingShipSystems(int $webId): array;

    public function truncateByShip(int $shipId): void;
}
