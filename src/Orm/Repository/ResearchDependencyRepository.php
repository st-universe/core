<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Research\ResearchModeEnum;
use Stu\Orm\Entity\ResearchDependency;

/**
 * @extends EntityRepository<ResearchDependency>
 */
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
            'mode' => ResearchModeEnum::EXCLUDE->value
        ]);
    }
}
