<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceApplication;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<AllianceApplication>
 */
final class AllianceApplicationRepository extends EntityRepository implements AllianceApplicationRepositoryInterface
{
    #[Override]
    public function prototype(): AllianceApplication
    {
        return new AllianceApplication();
    }

    #[Override]
    public function save(AllianceApplication $application): void
    {
        $em = $this->getEntityManager();
        $em->persist($application);
    }

    #[Override]
    public function delete(AllianceApplication $application): void
    {
        $em = $this->getEntityManager();
        $em->remove($application);
    }

    #[Override]
    public function getByAlliance(int $allianceId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.alliance = :allianceId')
            ->setParameter('allianceId', $allianceId)
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    #[Override]
    public function getByUserAndAlliance(User $user, Alliance $alliance): ?AllianceApplication
    {
        return $this->findOneBy([
            'user' => $user,
            'alliance' => $alliance,
        ]);
    }

    #[Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s a WHERE a.user = :userId',
                AllianceApplication::class
            )
        )->setParameters([
            'userId' => $userId,
        ])->execute();
    }

    #[Override]
    public function truncateByAlliance(int $allianceId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s a WHERE a.alliance = :allianceId',
                AllianceApplication::class
            )
        )->setParameters([
            'allianceId' => $allianceId,
        ])->execute();
    }
}
