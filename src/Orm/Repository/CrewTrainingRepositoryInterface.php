<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewTrainingInterface;

interface CrewTrainingRepositoryInterface extends ObjectRepository
{
    public function save(CrewTrainingInterface $researched): void;

    public function delete(CrewTrainingInterface $researched): void;

    public function prototype(): CrewTrainingInterface;

    public function truncateByColony(int $colonyId): void;

    public function getCountByUser(int $userId): int;

    /**
     * @return CrewTrainingInterface[]
     */
    public function getByTick(int $tickId): array;
}