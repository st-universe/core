<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\BlockedUser;
use Stu\Orm\Entity\BlockedUserInterface;

/**
 * @extends EntityRepository<BlockedUser>
 */
final class BlockedUserRepository extends EntityRepository implements BlockedUserRepositoryInterface
{
    #[Override]
    public function getByEmailHash(string $emailHash): ?BlockedUserInterface
    {
        return $this->findOneBy([
            'email_hash' => $emailHash
        ]);
    }

    #[Override]
    public function getByMobileHash(string $mobileHash): ?BlockedUserInterface
    {
        return $this->findOneBy([
            'mobile_hash' => $mobileHash
        ]);
    }

    #[Override]
    public function save(BlockedUserInterface $blockedUser): void
    {
        $em = $this->getEntityManager();

        $em->persist($blockedUser);
    }

    #[Override]
    public function prototype(): BlockedUserInterface
    {
        return new BlockedUser();
    }
}
