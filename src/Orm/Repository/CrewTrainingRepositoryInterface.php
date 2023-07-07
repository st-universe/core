<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CrewTraining;
use Stu\Orm\Entity\CrewTrainingInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<CrewTraining>
 */
interface CrewTrainingRepositoryInterface extends ObjectRepository
{
    public function save(CrewTrainingInterface $researched): void;

    public function delete(CrewTrainingInterface $researched): void;

    public function prototype(): CrewTrainingInterface;

    public function truncateByColony(ColonyInterface $colony): void;

    public function getCountByUser(UserInterface $user): int;

    /**
     * @return list<CrewTrainingInterface>
     */
    public function getByBatchGroup(int $batchGroup, int $batchGroupCount): array;
}
