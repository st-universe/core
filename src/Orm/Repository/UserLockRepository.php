<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLock;

/**
 * @extends EntityRepository<UserLock>
 */
final class UserLockRepository extends EntityRepository implements UserLockRepositoryInterface
{
    #[Override]
    public function getActiveByUser(User $user): ?UserLock
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ul FROM %s ul WHERE ul.user = :user
                ORDER BY ul.id DESC',
                UserLock::class
            )
        )->setParameters([
            'user' => $user
        ])
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    #[Override]
    public function getActive(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT ul FROM %s ul
                WHERE ul.user IS NOT NULL
                AND ul.remaining_ticks > 0',
                UserLock::class
            )
        )->getResult();
    }

    #[Override]
    public function save(UserLock $award): void
    {
        $em = $this->getEntityManager();

        $em->persist($award);
    }

    #[Override]
    public function prototype(): UserLock
    {
        return new UserLock();
    }
}
