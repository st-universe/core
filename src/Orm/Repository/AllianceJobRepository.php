<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AllianceJob;

/**
 * @extends EntityRepository<AllianceJob>
 */
final class AllianceJobRepository extends EntityRepository implements AllianceJobRepositoryInterface
{
    #[\Override]
    public function prototype(): AllianceJob
    {
        return new AllianceJob();
    }

    #[\Override]
    public function save(AllianceJob $post): void
    {
        $em = $this->getEntityManager();
        $em->persist($post);
    }

    #[\Override]
    public function delete(AllianceJob $post): void
    {
        $em = $this->getEntityManager();
        $em->remove($post);
    }

    #[\Override]
    public function getByAlliance(int $allianceId): array
    {
        return $this->createQueryBuilder('aj')
            ->where('aj.alliance = :allianceId')
            ->setParameter('allianceId', $allianceId)
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function truncateByAlliance(int $allianceId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s aj WHERE aj.alliance = :allianceId',
                AllianceJob::class
            )
        )->setParameters([
            'allianceId' => $allianceId,
        ])->execute();
    }

    #[\Override]
    public function getJobsWithFounderPermission(int $allianceId): array
    {
        return $this->createQueryBuilder('aj')
            ->where('aj.alliance = :allianceId')
            ->andWhere('aj.is_founder_permission = true')
            ->setParameter('allianceId', $allianceId)
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function getJobsWithSuccessorPermission(int $allianceId): array
    {
        return $this->createQueryBuilder('aj')
            ->where('aj.alliance = :allianceId')
            ->andWhere('aj.is_successor_permission = true')
            ->setParameter('allianceId', $allianceId)
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function getJobsWithDiplomaticPermission(int $allianceId): array
    {
        return $this->createQueryBuilder('aj')
            ->where('aj.alliance = :allianceId')
            ->andWhere('aj.is_diplomatic_permission = true')
            ->setParameter('allianceId', $allianceId)
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function getByAllianceAndTitle(int $allianceId, string $title): ?AllianceJob
    {
        return $this->createQueryBuilder('aj')
            ->where('aj.alliance = :allianceId')
            ->andWhere('aj.title = :title')
            ->setParameter('allianceId', $allianceId)
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
