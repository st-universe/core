<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipSystemInterface;

interface ShipSystemRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipSystemInterface;

    public function save(ShipSystemInterface $post): void;

    public function delete(ShipSystemInterface $post): void;

    /**
     * @return ShipSystemInterface[]
     */
    public function getByShip(int $shipId): array;

    /**
     * @return ShipSystemInterface[]
     */
    public function getTrackingShipSystems(int $targetId): array;

    public function truncateByShip(int $shipId): void;
}
