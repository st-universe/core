<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\PrestigeLog;
use Stu\Orm\Entity\PrestigeLogInterface;
use Stu\Orm\Entity\UserInterface;

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
}
