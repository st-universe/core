<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserLock;
use Stu\Orm\Entity\UserLockInterface;

final class UserLockRepository extends EntityRepository implements UserLockRepositoryInterface
{
    public function getByUser(int $userId): ?UserLockInterface
    {
        return $this->findOneBy([
            'user_id' => $userId,
        ]);
    }

    public function save(UserLockInterface $award): void
    {
        $em = $this->getEntityManager();

        $em->persist($award);
    }

    public function delete(UserLockInterface $award): void
    {
        $em = $this->getEntityManager();

        $em->remove($award);
        $em->flush();
    }

    public function prototype(): UserLockInterface
    {
        return new UserLock();
    }
}
