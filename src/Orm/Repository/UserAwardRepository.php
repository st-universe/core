<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\UserAward;

/**
 * @extends EntityRepository<UserAward>
 */
final class UserAwardRepository extends EntityRepository implements UserAwardRepositoryInterface
{
    #[Override]
    public function save(UserAward $award): void
    {
        $em = $this->getEntityManager();

        $em->persist($award);
    }

    #[Override]
    public function delete(UserAward $award): void
    {
        $em = $this->getEntityManager();

        $em->remove($award);
        $em->flush();
    }

    #[Override]
    public function prototype(): UserAward
    {
        return new UserAward();
    }
}
