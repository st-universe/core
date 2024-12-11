<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserReferer;
use Stu\Orm\Entity\UserRefererInterface;
use Override;

/**
 * @extends EntityRepository<UserReferer>
 */
final class UserRefererRepository extends EntityRepository implements UserRefererRepositoryInterface
{
    #[Override]
    public function prototype(): UserRefererInterface
    {
        return new UserReferer();
    }

    #[Override]
    public function save(UserRefererInterface $referer): void
    {
        $em = $this->getEntityManager();
        $em->persist($referer);
        $em->flush();
    }

    #[Override]
    public function delete(UserRefererInterface $referer): void
    {
        $em = $this->getEntityManager();
        $em->remove($referer);
        $em->flush();
    }

    #[Override]
    public function truncateAll(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ur',
                UserReferer::class
            )
        )->execute();
    }
}
