<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PrestigeLog;
use Stu\Orm\Entity\PrestigeLogInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<PrestigeLog>
 */
final class PrestigeLogRepository extends EntityRepository implements PrestigeLogRepositoryInterface
{

    public function save(PrestigeLogInterface $log): void
    {
        $em = $this->getEntityManager();

        $em->persist($log);
    }

    public function delete(PrestigeLogInterface $log): void
    {
        $em = $this->getEntityManager();

        $em->remove($log);
    }

    public function prototype(): PrestigeLogInterface
    {
        return new PrestigeLog();
    }

    public function getSumByUser(UserInterface $user): int
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

    public function getPrestigeHistory(UserInterface $user, int $maxResults): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT pl FROM %s pl
                WHERE pl.user_id = :userId
                ORDER BY pl.id DESC',
                PrestigeLog::class
            )
        )->setParameters([
            'userId' => $user->getId()
        ])->setMaxResults($maxResults)
            ->getResult();
    }
}
