<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ShipStorageInterface;

interface ShipStorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): ShipStorageInterface;

    public function save(ShipStorageInterface $shipStorage): void;

    public function delete(ShipStorageInterface $shipStorage): void;

    public function getByUserAndCommodity(int $userId, int $commodityId): iterable;
}
