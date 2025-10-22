<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\AllianceMemberJob;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<AllianceMemberJob>
 */
final class AllianceMemberJobRepository extends EntityRepository implements AllianceMemberJobRepositoryInterface
{
    #[Override]
    public function prototype(): AllianceMemberJob
    {
        return new AllianceMemberJob();
    }

    #[Override]
    public function save(AllianceMemberJob $allianceMemberJob): void
    {
        $em = $this->getEntityManager();
        $em->persist($allianceMemberJob);
    }

    #[Override]
    public function delete(AllianceMemberJob $allianceMemberJob): void
    {
        $em = $this->getEntityManager();
        $em->remove($allianceMemberJob);
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->createQueryBuilder('amj')
            ->where('amj.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    #[Override]
    public function getByJob(int $jobId): array
    {
        return $this->createQueryBuilder('amj')
            ->where('amj.job = :jobId')
            ->setParameter('jobId', $jobId)
            ->getQuery()
            ->getResult();
    }

    #[Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s amj WHERE amj.user = :userId',
                AllianceMemberJob::class
            )
        )->setParameters([
            'userId' => $userId,
        ])->execute();
    }

    #[Override]
    public function truncateByJob(int $jobId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s amj WHERE amj.job = :jobId',
                AllianceMemberJob::class
            )
        )->setParameters([
            'jobId' => $jobId,
        ])->execute();
    }

    #[Override]
    public function getByUserAndJob(User $user, int $jobId): ?AllianceMemberJob
    {
        return $this->findOneBy([
            'user' => $user,
            'job' => $jobId,
        ]);
    }
}
