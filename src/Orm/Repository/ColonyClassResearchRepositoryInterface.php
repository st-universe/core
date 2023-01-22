<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyClassInterface;
use Stu\Orm\Entity\ColonyClassResearch;
use Stu\Orm\Entity\ColonyClassResearchInterface;

/**
 * @extends ObjectRepository<ColonyClassResearch>
 */
interface ColonyClassResearchRepositoryInterface extends ObjectRepository
{
    /**
     * @return ColonyClassResearchInterface[]
     */
    public function getByColonyClass(ColonyClassInterface $colonyClass): array;
}
