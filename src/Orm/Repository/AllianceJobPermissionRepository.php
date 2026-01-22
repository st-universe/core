<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AllianceJobPermission;

/**
 * @extends EntityRepository<AllianceJobPermission>
 */
final class AllianceJobPermissionRepository extends EntityRepository implements AllianceJobPermissionRepositoryInterface
{
    #[\Override]
    public function prototype(): AllianceJobPermission
    {
        return new AllianceJobPermission();
    }

    #[\Override]
    public function save(AllianceJobPermission $permission): void
    {
        $em = $this->getEntityManager();
        $em->persist($permission);
    }

    #[\Override]
    public function delete(AllianceJobPermission $permission): void
    {
        $em = $this->getEntityManager();
        $em->remove($permission);
    }

    #[\Override]
    public function getByJob(int $jobId): array
    {
        return $this->createQueryBuilder('ajp')
            ->where('ajp.job = :jobId')
            ->setParameter('jobId', $jobId)
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function deleteByJob(int $jobId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ajp WHERE ajp.job = :jobId',
                AllianceJobPermission::class
            )
        )->setParameter('jobId', $jobId)
            ->execute();
    }
}
