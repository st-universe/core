<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserTag;

/**
 * @extends EntityRepository<UserTag>
 */
final class UserTagRepository extends EntityRepository implements UserTagRepositoryInterface
{
    #[Override]
    public function getByUser(User $user): iterable
    {
        return $this->findBy([
            'user' => $user
        ]);
    }

    #[Override]
    public function prototype(): UserTag
    {
        return new UserTag();
    }

    #[Override]
    public function save(UserTag $userTag): void
    {
        $em = $this->getEntityManager();

        $em->persist($userTag);
        $em->flush();
    }
}
