<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\BlockedUser;
use Stu\Orm\Entity\BlockedUserInterface;

final class BlockedUserRepository extends EntityRepository implements BlockedUserRepositoryInterface
{
    public function getByEmail(string $emailHash): ?BlockedUserInterface
    {
        return $this->findOneBy([
            'email' => $emailHash
        ]);
    }

    public function getByMobile(string $mobileHash): ?BlockedUserInterface
    {
        return $this->findOneBy([
            'mobile' => $mobileHash
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
