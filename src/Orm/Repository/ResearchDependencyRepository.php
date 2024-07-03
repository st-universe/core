<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Component\Research\ResearchModeEnum;
use Stu\Orm\Entity\ResearchDependency;

/**
 * @extends EntityRepository<ResearchDependency>
 */
final class ResearchDependencyRepository extends EntityRepository implements ResearchDependencyRepositoryInterface
{
    #[Override]
    public function getByMode(array $modes): array
    {
        return $this->findBy([
            'mode' => $modes
        ]);
    }

    #[Override]
    public function getExcludesByResearch(int $researchId): array
    {
        return $this->findBy([
            'research_id' => $researchId,
            'mode' => ResearchModeEnum::EXCLUDE->value
        ]);
    }

    #[Override]
    public function getByDependingResearch(int $researchId): array
    {
        return $this->findBy([
            'depends_on' => $researchId
        ]);
    }
}
