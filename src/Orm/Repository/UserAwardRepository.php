<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserAward;
use Stu\Orm\Entity\UserAwardInterface;

/**
 * @extends EntityRepository<UserAward>
 */
final class UserAwardRepository extends EntityRepository implements UserAwardRepositoryInterface
{
    #[Override]
    public function save(UserAwardInterface $award): void
    {
        $em = $this->getEntityManager();

        $em->persist($award);
    }

    #[Override]
    public function delete(UserAwardInterface $award): void
    {
        $em = $this->getEntityManager();

        $em->remove($award);
        $em->flush();
    }

    #[Override]
    public function prototype(): UserAwardInterface
    {
        return new UserAward();
    }
}
