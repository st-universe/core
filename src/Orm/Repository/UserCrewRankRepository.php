<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserCrewRank;

/**
 * @extends EntityRepository<UserCrewRank>
 */
final class UserCrewRankRepository extends EntityRepository implements UserCrewRankRepositoryInterface
{
    #[\Override]
    public function prototype(): UserCrewRank
    {
        return new UserCrewRank();
    }

    #[\Override]
    public function save(UserCrewRank $userCrewRank): void
    {
        $this->getEntityManager()->persist($userCrewRank);
    }

    #[\Override]
    public function delete(UserCrewRank $userCrewRank): void
    {
        $this->getEntityManager()->remove($userCrewRank);
    }

    #[\Override]
    public function truncateByUser(User $user): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ucr WHERE ucr.user = :user',
                UserCrewRank::class
            )
        )->setParameter('user', $user)->execute();
    }
}
