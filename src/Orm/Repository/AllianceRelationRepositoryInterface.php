<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\AllianceRelationInterface;

/**
 * @extends ObjectRepository<AllianceRelation>
 *
 * @method null|AllianceRelationInterface find(integer $id)
 */
interface AllianceRelationRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceRelationInterface;

    public function save(AllianceRelationInterface $post): void;

    public function delete(AllianceRelationInterface $post): void;

    public function truncateByAlliances(AllianceInterface $alliance, AllianceInterface $opponent): void;

    public function getPendingCountByAlliances(int $allianceId, int $opponentId): int;

    /**
     * @return array<AllianceRelationInterface>
     */
    public function getByAlliancePair(int $allianceId, int $opponentId): array;

    /**
     * @return array<AllianceRelationInterface>
     */
    public function getActiveByAlliance(int $allianceId): array;

    /**
     * @return array<AllianceRelationInterface>
     */
    public function getByAlliance(int $allianceId): array;

    public function getActiveByAlliancePair(int $allianceId, int $opponentId): ?AllianceRelationInterface;

    /**
     * @param array<int> $typeIds
     */
    public function getActiveByTypeAndAlliancePair(array $typeIds, int $allianceId, int $opponentId): ?AllianceRelationInterface;

    public function truncateAllAllianceRelations(): void;
}
