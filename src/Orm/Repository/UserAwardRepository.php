<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserAward;
use Stu\Orm\Entity\UserAwardInterface;

/**
 * @extends EntityRepository<UserAward>
 */
final class UserAwardRepository extends EntityRepository implements UserAwardRepositoryInterface
{

    public function save(UserAwardInterface $award): void
    {
        $em = $this->getEntityManager();

        $em->persist($award);
    }

    public function delete(UserAwardInterface $award): void
    {
        $em = $this->getEntityManager();

        $em->remove($award);
        $em->flush();
    }

    public function prototype(): UserAwardInterface
    {
        return new UserAward();
    }
}
