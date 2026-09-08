<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Relation>
 *
 * @method null|Relation find(integer $id)
 */
interface RelationRepositoryInterface extends ObjectRepository
{
    public function prototype(): Relation;

    public function save(Relation $relation): void;

    public function delete(Relation $relation): void;

    public function truncateByUser(User $user): void;

    public function truncateByAlliance(Alliance $alliance): void;

    public function truncateByAlliances(Alliance $alliance, Alliance $opponent): void;

    /** @return array<int, Relation> */
    public function getActive(): array;

    /**
     * @param array<int, int> $typeIds
     * @return array<int, Relation>
     */
    public function getActiveByTypes(array $typeIds): array;

    /** @return array<int, Relation> */
    public function getByAlliance(int $allianceId): array;

    /** @return array<int, Relation> */
    public function getByAlliancePair(int $allianceId, int $opponentId): array;

    public function getPendingCountByAlliances(int $allianceId, int $opponentId): int;

    /** @return array<int, Relation> */
    public function getActiveByAlliance(int $allianceId): array;

    public function getActiveByAlliancePair(int $allianceId, int $opponentId): ?Relation;

    /** @param array<int, int> $typeIds */
    public function getActiveByParties(
        array $typeIds,
        User|Alliance $firstParty,
        User|Alliance $secondParty
    ): ?Relation;

    /** @param array<int, int> $typeIds */
    public function getActiveByTypeAndAlliancePair(
        array $typeIds,
        int $allianceId,
        int $opponentId
    ): ?Relation;

    /** @return array<int, Relation> */
    public function getByUserAndAlliance(User $user, ?Alliance $alliance): array;

    /** @return array<int, Relation> */
    public function getByUserPair(User $firstUser, User $secondUser): array;

    /** @return array<int, Relation> */
    public function getByAllianceAndUserPair(Alliance $alliance, User $user): array;

    /** @param array<int, int> $typeIds */
    public function getActiveByUserPair(array $typeIds, User $firstUser, User $secondUser): ?Relation;

    /** @param array<int, int> $typeIds */
    public function getActiveByAllianceAndUserPair(array $typeIds, Alliance $alliance, User $user): ?Relation;
}
