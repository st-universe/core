<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\AllianceRelationInterface;

final class AllianceRelationRepository extends EntityRepository implements AllianceRelationRepositoryInterface
{

    public function prototype(): AllianceRelationInterface
    {
        return new AllianceRelation();
    }

    public function save(AllianceRelationInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(AllianceRelationInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function truncateByAlliances(int $allianceId, int $opponentId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s ar WHERE ar.alliance_id IN (:allianceId,:opponentId) AND ar.recipient IN (:allianceId,:opponentId)',
                    AllianceRelation::class
                )
            )
            ->setParameters([
                'allianceId' => $allianceId,
                'opponentId' => $opponentId
            ])
            ->execute();
    }

    public function getPendingCountByAlliances(int $allianceId, int $opponentId): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT COUNT(ar.id) FROM %s ar WHERE ar.date = :date AND (
                        ar.alliance_id IN (:allianceId,:opponentId) AND ar.recipient IN (:allianceId,:opponentId)
                    )',
                    AllianceRelation::class
                )
            )
            ->setParameters([
                'date' => 0,
                'allianceId' => $allianceId,
                'opponentId' => $opponentId
            ])
            ->getSingleScalarResult();
    }

    public function getByAlliancePair(int $allianceId, int $opponentId): ?AllianceRelationInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ar FROM %s ar WHERE ar.alliance_id IN (:allianceId,:opponentId) AND ar.recipient IN (:allianceId,:opponentId)',
                    AllianceRelation::class
                )
            )
            ->setParameters([
                'allianceId' => $allianceId,
                'opponentId' => $opponentId
            ])
            ->getOneOrNullResult();
    }

    public function getActiveByAlliance(int $allianceId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ar FROM %s ar WHERE ar.date > 0 AND (ar.alliance_id = :allianceId OR ar.recipient = :allianceId)',
                    AllianceRelation::class
                )
            )
            ->setParameters([
                'allianceId' => $allianceId
            ])
            ->getResult();
    }

    public function getByAlliance(int $allianceId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ar FROM %s ar WHERE ar.alliance_id = :allianceId OR ar.recipient = :allianceId',
                    AllianceRelation::class
                )
            )
            ->setParameters([
                'allianceId' => $allianceId
            ])
            ->getResult();
    }

    public function getActiveByAlliancePair(int $allianceId, int $opponentId): ?AllianceRelationInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ar FROM %s ar WHERE ar.date > 0 AND ar.alliance_id IN (:allianceId,:opponentId) AND ar.recipient IN (:allianceId,:opponentId)',
                    AllianceRelation::class
                )
            )
            ->setParameters([
                'allianceId' => $allianceId,
                'opponentId' => $opponentId
            ])
            ->getOneOrNullResult();
    }


    public function getActiveByTypeAndAlliancePair(array $typeIds, int $allianceId, int $opponentId): ?AllianceRelationInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ar FROM %s ar WHERE ar.type IN (:typeIds) AND ar.date > 0 AND ar.alliance_id IN (:allianceId,:opponentId) AND ar.recipient IN (:allianceId,:opponentId)',
                    AllianceRelation::class
                )
            )
            ->setParameters([
                'allianceId' => $allianceId,
                'opponentId' => $opponentId,
                'typeIds' => $typeIds
            ])
            ->getOneOrNullResult();
    }
}
