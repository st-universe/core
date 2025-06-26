<?php

namespace Stu\Module\Research;

use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\User;

interface TechlistRetrieverInterface
{
    /**
     * @return Research[]
     */
    public function getResearchList(User $user): array;

    public function canResearch(User $user, int $researchId): ?Research;

    /**
     * @return Researched[]
     */
    public function getResearchedList(User $user): array;
}
