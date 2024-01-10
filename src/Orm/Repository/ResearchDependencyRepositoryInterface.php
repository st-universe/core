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
     * @param array<int> $modes
     * 
     * @return array<ResearchDependencyInterface>
     */
    public function getByMode(array $modes): array;

    /**
     * @return array<ResearchDependencyInterface>
     */
    public function getExcludesByResearch(int $researchId): array;

    /**
     * @return array<ResearchDependencyInterface>
     */
    public function getByDependingResearch(int $researchId): array;
}
