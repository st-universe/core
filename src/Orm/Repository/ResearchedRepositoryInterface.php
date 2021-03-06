<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\UserInterface;

interface ResearchedRepositoryInterface extends ObjectRepository
{
    /**
     * @param int[] $researchIds
     */
    public function hasUserFinishedResearch(UserInterface $user, array $researchIds): bool;

    /**
     * @return ResearchedInterface[]
     */
    public function getListByUser(int $userId): array;

    /**
     * @return ResearchedInterface[]
     */
    public function getFinishedListByUser(int $userId): array;

    public function getCurrentResearch(int $userId): ?ResearchedInterface;

    public function getFor(int $researchId, int $userId): ?ResearchedInterface;

    public function save(ResearchedInterface $researched): void;

    public function delete(ResearchedInterface $researched): void;

    public function prototype(): ResearchedInterface;

    public function truncateForUser(int $userId): void;
}
