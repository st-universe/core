<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserTag;
use Stu\Orm\Entity\UserTagInterface;

final class UserTagRepository extends EntityRepository implements UserTagRepositoryInterface
{

    public function prototype(): UserTagInterface
    {
        return new UserTag();
    }

    public function save(UserTagInterface $userTag): void
    {
        $em = $this->getEntityManager();

        $em->persist($userTag);
        $em->flush($userTag);
    }
}
