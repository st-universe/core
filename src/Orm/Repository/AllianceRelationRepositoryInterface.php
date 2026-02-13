<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;

/**
 * @extends ObjectRepository<AllianceRelation>
 *
 * @method null|AllianceRelation find(integer $id)
 */
interface AllianceRelationRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceRelation;

    public function save(AllianceRelation $post): void;

    public function delete(AllianceRelation $post): void;

    public function truncateByAlliances(Alliance $alliance, Alliance $opponent): void;

    public function getPendingCountByAlliances(int $allianceId, int $opponentId): int;

    /**
     * @return array<AllianceRelation>
     */
    public function getActive(): array;

    /**
     * @return array<AllianceRelation>
     */
    public function getByAlliancePair(int $allianceId, int $opponentId): array;

    /**
     * @return array<AllianceRelation>
     */
    public function getActiveByAlliance(int $allianceId): array;

    /**
     * @return array<AllianceRelation>
     */
    public function getByAlliance(int $allianceId): array;

    public function getActiveByAlliancePair(int $allianceId, int $opponentId): ?AllianceRelation;

    /**
     * @param array<int> $typeIds
     */
    public function getActiveByTypeAndAlliancePair(array $typeIds, int $allianceId, int $opponentId): ?AllianceRelation;
}
