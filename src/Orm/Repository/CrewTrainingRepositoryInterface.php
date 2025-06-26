<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\CrewTraining;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<CrewTraining>
 */
interface CrewTrainingRepositoryInterface extends ObjectRepository
{
    public function save(CrewTraining $researched): void;

    public function delete(CrewTraining $researched): void;

    public function prototype(): CrewTraining;

    public function truncateByColony(Colony $colony): void;

    public function getCountByUser(User $user): int;

    /**
     * @return list<CrewTraining>
     */
    public function getByBatchGroup(int $batchGroup, int $batchGroupCount): array;
}
