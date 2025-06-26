<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\ColonyClassResearch;

/**
 * @extends ObjectRepository<ColonyClassResearch>
 */
interface ColonyClassResearchRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<ColonyClassResearch>
     */
    public function getByColonyClass(ColonyClass $colonyClass): array;
}
