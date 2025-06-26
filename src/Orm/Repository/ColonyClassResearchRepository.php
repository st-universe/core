<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\ColonyClassResearch;

/**
 * @extends EntityRepository<ColonyClassResearch>
 */
final class ColonyClassResearchRepository extends EntityRepository implements ColonyClassResearchRepositoryInterface
{
    #[Override]
    public function getByColonyClass(ColonyClass $colonyClass): array
    {
        return $this->findBy([
            'colonyClass' => $colonyClass
        ]);
    }
}
