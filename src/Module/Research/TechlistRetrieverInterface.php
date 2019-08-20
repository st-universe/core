<?php

namespace Stu\Module\Research;

use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;

interface TechlistRetrieverInterface
{
    /**
     * @return ResearchInterface[]
     */
    public function getResearchList(int $userId): array;

    /**
     * @return ResearchedInterface[]
     */
    public function getFinishedResearchList(int $userId): array;
}