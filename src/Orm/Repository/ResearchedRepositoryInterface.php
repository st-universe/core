<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<Researched>
 */
interface ResearchedRepositoryInterface extends ObjectRepository
{
    /**
     * @param array<int> $researchIds
     */
    public function hasUserFinishedResearch(UserInterface $user, array $researchIds): bool;

    /**
     * @return array<ResearchedInterface>
     */
    public function getListByUser(int $userId): array;

    /**
     * @return array<ResearchedInterface>
     */
    public function getFinishedListByUser(int $userId): array;

    public function getCurrentResearch(UserInterface $user): ?ResearchedInterface;

    public function getFor(int $researchId, int $userId): ?ResearchedInterface;

    public function save(ResearchedInterface $researched): void;

    public function delete(ResearchedInterface $researched): void;

    public function prototype(): ResearchedInterface;

    public function truncateForUser(int $userId): void;

    /**
     * @return array<array{user_id: int, points: int}>
     */
    public function getResearchedPoints(): array;
}
