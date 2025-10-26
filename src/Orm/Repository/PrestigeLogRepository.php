<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PrestigeLog;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<PrestigeLog>
 */
final class PrestigeLogRepository extends EntityRepository implements PrestigeLogRepositoryInterface
{
    #[\Override]
    public function save(PrestigeLog $log): void
    {
        $em = $this->getEntityManager();

        $em->persist($log);
    }

    #[\Override]
    public function delete(PrestigeLog $log): void
    {
        $em = $this->getEntityManager();

        $em->remove($log);
    }

    #[\Override]
    public function prototype(): PrestigeLog
    {
        return new PrestigeLog();
    }

    #[\Override]
    public function getSumByUser(User $user): int
    {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT SUM(pl.amount) FROM %s pl
                WHERE pl.user_id = :userId',
                PrestigeLog::class
            )
        )->setParameters([
            'userId' => $user->getId()
        ])->getSingleScalarResult();
    }

    #[\Override]
    public function getPrestigeHistory(User $user, int $maxResults): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT pl FROM %s pl
                    WHERE pl.user_id = :userId
                    ORDER BY pl.id DESC',
                    PrestigeLog::class
                )
            )
            ->setParameters([
                'userId' => $user->getId()
            ])
            ->setMaxResults($maxResults)
            ->getResult();
    }
}
