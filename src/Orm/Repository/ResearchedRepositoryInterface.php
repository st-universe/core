<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Researched>
 */
interface ResearchedRepositoryInterface extends ObjectRepository
{
    /**
     * @param array<int> $researchIds
     */
    public function hasUserFinishedResearch(User $user, array $researchIds): bool;

    /**
     * @return array<Researched>
     */
    public function getListByUser(int $userId): array;

    /**
     * @return array<Researched>
     */
    public function getFinishedListByUser(int $userId): array;

    /**
     * @return array<Researched>
     */
    public function getCurrentResearch(User $user): array;

    public function getFor(int $researchId, int $userId): ?Researched;

    public function save(Researched $researched): void;

    public function delete(Researched $researched): void;

    public function prototype(): Researched;

    public function truncateForUser(int $userId): void;

    /**
     * @return array<array{user_id: int, points: int, timestamp: int}>
     */
    public function getResearchedPoints(): array;
}
