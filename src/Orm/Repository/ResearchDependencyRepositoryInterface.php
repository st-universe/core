<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ResearchDependency;

/**
 * @extends ObjectRepository<ResearchDependency>
 */
interface ResearchDependencyRepositoryInterface extends ObjectRepository
{
    /**
     * @param array<int> $modes
     *
     * @return array<ResearchDependency>
     */
    public function getByMode(array $modes): array;

    /**
     * @return array<ResearchDependency>
     */
    public function getExcludesByResearch(int $researchId): array;

    /**
     * @return array<ResearchDependency>
     */
    public function getByDependingResearch(int $researchId): array;
}
