<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceRelationInterface;

/**
 * @method null|AllianceRelationInterface find(integer $id)
 */
interface AllianceRelationRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceRelationInterface;

    public function save(AllianceRelationInterface $post): void;

    public function delete(AllianceRelationInterface $post): void;

    public function truncateByAlliances(int $allianceId, int $opponentId): void;

    public function getPendingCountByAlliances(int $allianceId, int $opponentId): int;

    public function getByAlliancePair(int $allianceId, int $opponentId): ?AllianceRelationInterface;

    /**
     * @return AllianceRelationInterface[]
     */
    public function getActiveByAlliance(int $allianceId): array;

    /**
     * @return AllianceRelationInterface[]
     */
    public function getActiveIsVassal(int $allianceId): array;

    /**
     * @return AllianceRelationInterface[]
     */
    public function getActiveHasVassal(int $allianceId): array;

    /**
     * @return AllianceRelationInterface[]
     */
    public function getByAlliance(int $allianceId): array;

    public function getActiveByAlliancePair(int $allianceId, int $opponentId): ?AllianceRelationInterface;

    public function getActiveByTypeAndAlliancePair(array $typeIds, int $allianceId, int $opponentId): ?AllianceRelationInterface;
}