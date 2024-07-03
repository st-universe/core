<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\AllianceRelationInterface;

/**
 * @extends EntityRepository<AllianceRelation>
 */
final class AllianceRelationRepository extends EntityRepository implements AllianceRelationRepositoryInterface
{
    #[Override]
    public function prototype(): AllianceRelationInterface
    {
        return new AllianceRelation();
    }

    #[Override]
    public function save(AllianceRelationInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(AllianceRelationInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function truncateByAlliances(AllianceInterface $alliance, AllianceInterface $opponent): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s ar WHERE ar.alliance IN (:alliance,:opponent) AND ar.opponent IN (:alliance,:opponent)',
                    AllianceRelation::class
                )
            )
            ->setParameters([
                'alliance' => $alliance,
                'opponent' => $opponent
            ])
            ->execute();
    }

    #[Override]
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

    #[Override]
    public function getByAlliancePair(int $allianceId, int $opponentId): array
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
            ->getResult();
    }


    #[Override]
    public function getActiveByAlliance(int $allianceId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ar FROM %s ar
                    WHERE ar.date > 0 AND (ar.alliance_id = :allianceId OR ar.recipient = :allianceId)
                    ORDER BY ar.id ASC',
                    AllianceRelation::class
                )
            )
            ->setParameters([
                'allianceId' => $allianceId
            ])
            ->getResult();
    }

    #[Override]
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

    #[Override]
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


    #[Override]
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

    #[Override]
    public function truncateAllAllianceRelations(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ar',
                AllianceRelation::class
            )
        )->execute();
    }
}
