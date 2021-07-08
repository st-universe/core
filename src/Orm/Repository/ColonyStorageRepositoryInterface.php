<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyStorageInterface;

interface ColonyStorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyStorageInterface;

    public function flush(): void;

    public function save(ColonyStorageInterface $post, bool $flush = true): void;

    public function delete(ColonyStorageInterface $post): void;

    /**
     * @return ColonyStorageInterface[]
     */
    public function getByColony(int $colonyId, int $viewable = 1): array;

    public function getByUserAccumulated(int $userId): iterable;

    public function getByUserAndCommodity(int $userId, int $commodityId): iterable;

    public function truncateByColony(ColonyInterface $colony): void;
}
