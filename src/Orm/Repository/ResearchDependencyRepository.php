<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Research\ResearchEnum;

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
            'mode' => ResearchEnum::RESEARCH_MODE_EXCLUDE
        ]);
    }

    public function getRequirementsByResearch(int $researchId): array
    {
        return $this->findBy([
            'depends_on' => $researchId,
            'mode' => [ResearchEnum::RESEARCH_MODE_REQUIRE, ResearchEnum::RESEARCH_MODE_REQUIRE_SOME]
        ]);
    }
}
