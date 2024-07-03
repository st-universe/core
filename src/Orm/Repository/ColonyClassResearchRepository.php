<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\ColonyClassResearch;

/**
 * @extends EntityRepository<ColonyClassResearch>
 */
final class ColonyClassResearchRepository extends EntityRepository implements ColonyClassResearchRepositoryInterface
{
    #[Override]
    public function getByColonyClass(ColonyClassInterface $colonyClass): array
    {
        return $this->findBy([
            'planet_type_id' => $colonyClass
        ]);
    }
}
