<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipStorageInterface;

interface ShipStorageRepositoryInterface extends ObjectRepository
{
    /**
     * @return ShipStorageInterface[]
     */
    public function getByShip(int $shipId): array;

    public function prototype(): ShipStorageInterface;

    public function save(ShipStorageInterface $shipStorage): void;

    public function delete(ShipStorageInterface $shipStorage): void;

    public function getByUserAccumulated(int $userId): iterable;

    public function getByUserAndCommodity(int $userId, int $commodityId): iterable;

    public function truncateForShip(int $shipId): void;
}