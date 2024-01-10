<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<Research>
 *
 * @method null|ResearchInterface find(Integer $id)
 */
interface ResearchRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<ResearchInterface>
     */
    public function getAvailableResearch(int $userId): array;

    public function getColonyTypeLimitByUser(UserInterface $user, int $colonyType): int;

    /**
     * @return array<ResearchInterface>
     */
    public function getPossibleResearchByParent(int $researchId): array;

    public function save(ResearchInterface $research): void;
}
