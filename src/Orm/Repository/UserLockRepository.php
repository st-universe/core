<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserLock;
use Stu\Orm\Entity\UserLockInterface;

/**
 * @extends EntityRepository<UserLock>
 */
final class UserLockRepository extends EntityRepository implements UserLockRepositoryInterface
{
    public function getActiveByUser(int $userId): ?UserLockInterface
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ul FROM %s ul WHERE ul.user_id = :userId
                ORDER BY ul.id DESC',
                UserLock::class
            )
        )->setParameters([
            'userId' => $userId
        ])
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function getActive(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ul FROM %s ul
                WHERE ul.user_id IS NOT NULL
                AND ul.remaining_ticks > 0',
                UserLock::class
            )
        )->getResult();
    }

    public function save(UserLockInterface $award): void
    {
        $em = $this->getEntityManager();

        $em->persist($award);
    }

    public function prototype(): UserLockInterface
    {
        return new UserLock();
    }
}
