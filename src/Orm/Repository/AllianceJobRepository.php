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
            ->orderBy('aj.sort', 'ASC')
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
    public function getJobsWithPermission(int $allianceId, int $permissionType): array
    {
        return $this->createQueryBuilder('aj')
            ->innerJoin('aj.permissions', 'ajp')
            ->where('aj.alliance = :allianceId')
            ->andWhere('ajp.permission = :permissionType')
            ->setParameter('allianceId', $allianceId)
            ->setParameter('permissionType', $permissionType)
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
