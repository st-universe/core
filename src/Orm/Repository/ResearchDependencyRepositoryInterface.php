<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ResearchDependency;
use Stu\Orm\Entity\ResearchDependencyInterface;

/**
 * @extends ObjectRepository<ResearchDependency>
 */
interface ResearchDependencyRepositoryInterface extends ObjectRepository
{
    /**
     * @param array $modes array<int> expected
     * @return ResearchDependencyInterface[]
     */
    public function getByMode(array $modes): array;

    /**
     * @return ResearchDependencyInterface[]
     */
    public function getExcludesByResearch(int $researchId): array;
}
