<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Research>
 *
 * @method null|Research find(Integer $id)
 */
interface ResearchRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<Research>
     */
    public function getAvailableResearch(int $userId): array;

    public function getColonyTypeLimitByUser(User $user, int $colonyType): int;

    /**
     * @return array<Research>
     */
    public function getPossibleResearchByParent(int $researchId): array;

    public function save(Research $research): void;
}
