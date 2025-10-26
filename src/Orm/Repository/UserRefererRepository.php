<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserReferer;

/**
 * @extends EntityRepository<UserReferer>
 */
final class UserRefererRepository extends EntityRepository implements UserRefererRepositoryInterface
{
    #[\Override]
    public function prototype(): UserReferer
    {
        return new UserReferer();
    }

    #[\Override]
    public function save(UserReferer $referer): void
    {
        $em = $this->getEntityManager();
        $em->persist($referer);
        $em->flush();
    }

    #[\Override]
    public function delete(UserReferer $referer): void
    {
        $em = $this->getEntityManager();
        $em->remove($referer);
        $em->flush();
    }
}
