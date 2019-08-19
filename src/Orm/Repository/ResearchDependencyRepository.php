<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

final class ResearchDependencyRepository extends EntityRepository implements ResearchDependencyRepositoryInterface
{
    public function getByMode(array $modes): array
    {
        return $this->findBy([
            'mode' => $modes
        ]);
    }

    public function getExcludesByResearch(int $researchId): array
    {
        return $this->findBy([
            'research_id' => $researchId,
            'mode' => RESEARCH_MODE_EXCLUDE
        ]);
    }

    public function getRequirementsByResearch(int $researchId): array
    {
        return $this->findBy([
            'depends_on' => $researchId,
            'mode' => [RESEARCH_MODE_REQUIRE, RESEARCH_MODE_REQUIRE_SOME]
        ]);
    }
}