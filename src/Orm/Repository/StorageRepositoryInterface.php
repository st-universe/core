<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\StorageInterface;

interface StorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): StorageInterface;

    public function save(StorageInterface $storage): void;

    public function delete(StorageInterface $storage): void;

    public function getByUserAccumulated(int $userId): iterable;

    public function getColonyStorageByUserAndCommodity(int $userId, int $commodityId): iterable;

    public function getShipStorageByUserAndCommodity(int $userId, int $commodityId): iterable;
}
