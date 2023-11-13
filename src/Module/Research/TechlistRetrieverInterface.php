<?php

namespace Stu\Module\Research;

use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;

interface TechlistRetrieverInterface
{
    /**
     * @return ResearchInterface[]
     */
    public function getResearchList(UserInterface $user): array;

    public function canResearch(UserInterface $user, int $researchId): ?ResearchInterface;

    /**
     * @return ResearchedInterface[]
     */
    public function getResearchedList(UserInterface $user): array;
}
