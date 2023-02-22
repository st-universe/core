<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserTag;
use Stu\Orm\Entity\UserTagInterface;

/**
 * @extends EntityRepository<UserTag>
 */
final class UserTagRepository extends EntityRepository implements UserTagRepositoryInterface
{
    public function getByUser(UserInterface $user): iterable
    {
        return $this->findBy([
            'user' => $user,
        ]);
    }

    public function prototype(): UserTagInterface
    {
        return new UserTag();
    }

    public function save(UserTagInterface $userTag): void
    {
        $em = $this->getEntityManager();

        $em->persist($userTag);
        $em->flush();
    }
}
