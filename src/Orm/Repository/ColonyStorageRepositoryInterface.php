<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyStorageInterface;

interface ColonyStorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyStorageInterface;

    public function save(ColonyStorageInterface $post): void;

    public function delete(ColonyStorageInterface $post): void;

    public function getByUserAccumulated(int $userId): iterable;

    public function getByUserAndCommodity(int $userId, int $commodityId): iterable;

    public function truncateByColony(ColonyInterface $colony): void;
}
