<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ResearchInterface;

interface ResearchRepositoryInterface extends ObjectRepository
{
    /**
     * @return ResearchInterface[]
     */
    public function getAvailableResearch(int $userId): array;

    /**
     * @return ResearchInterface[]
     */
    public function getForFaction(int $factionId): array;
}