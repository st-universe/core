<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;

interface RelationRepositoryInterface extends ObjectRepository
{
    public function prototype(): Relation;

    public function save(Relation $relation): void;

    public function delete(Relation $relation): void;

    public function truncateByUser(User $user): void;

    public function truncateByAlliance(Alliance $alliance): void;

    public function truncateByAlliances(Alliance $alliance, Alliance $opponent): void;

    public function getActive(): array;

    public function getActiveByTypes(array $typeIds): array;

    public function getByAlliance(int $allianceId): array;

    public function getByAlliancePair(int $allianceId, int $opponentId): array;

    public function getPendingCountByAlliances(int $allianceId, int $opponentId): int;

    public function getActiveByAlliance(int $allianceId): array;

    public function getActiveByAlliancePair(int $allianceId, int $opponentId): ?Relation;

    public function getActiveByParties(
        array $typeIds,
        User|Alliance $firstParty,
        User|Alliance $secondParty
    ): ?Relation;

    public function getActiveByTypeAndAlliancePair(
        array $typeIds,
        int $allianceId,
        int $opponentId
    ): ?Relation;

    public function getByUserAndAlliance(User $user, ?Alliance $alliance): array;

    public function getByUserPair(User $firstUser, User $secondUser): array;

    public function getByAllianceAndUserPair(Alliance $alliance, User $user): array;

    public function getActiveByUserPair(array $typeIds, User $firstUser, User $secondUser): ?Relation;

    public function getActiveByAllianceAndUserPair(array $typeIds, Alliance $alliance, User $user): ?Relation;
}
