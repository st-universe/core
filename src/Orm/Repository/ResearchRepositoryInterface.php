<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use phpDocumentor\Reflection\Types\Integer;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @method null|ResearchInterface find(Integer $id)
 */
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

    public function getPlanetColonyLimitByUser(UserInterface $user): int;

    public function getMoonColonyLimitByUser(UserInterface $user): int;

    /**
     * @return ResearchInterface[]
     */
    public function getPossibleResearchByParent(int $researchId): array;

    public function save(ResearchInterface $research): void;
}
