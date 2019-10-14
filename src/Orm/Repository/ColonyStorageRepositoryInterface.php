<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyStorageInterface;

interface ColonyStorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyStorageInterface;

    public function save(ColonyStorageInterface $post): void;

    public function delete(ColonyStorageInterface $post): void;

    /**
     * @return ColonyStorageInterface[]
     */
    public function getByColony(int $colonyId, int $viewable = 1): array;

    public function truncateByColony(ColonyInterface $colony): void;
}
