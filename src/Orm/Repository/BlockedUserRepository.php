<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BlockedUser;
use Stu\Orm\Entity\BlockedUserInterface;

final class BlockedUserRepository extends EntityRepository implements BlockedUserRepositoryInterface
{
    public function getByEmail(string $email): ?BlockedUserInterface
    {
        return $this->findOneBy([
            'email' => sha1($email)
        ]);
    }

    public function getByMobile(string $mobile): ?BlockedUserInterface
    {
        return $this->findOneBy([
            'mobile' => sha1($mobile)
        ]);
    }

    public function save(BlockedUserInterface $blockedUser): void
    {
        $em = $this->getEntityManager();

        $em->persist($blockedUser);
    }

    public function prototype(): BlockedUserInterface
    {
        return new BlockedUser();
    }
}
