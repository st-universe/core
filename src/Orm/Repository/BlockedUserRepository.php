<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\BlockedUser;

/**
 * @extends EntityRepository<BlockedUser>
 */
final class BlockedUserRepository extends EntityRepository implements BlockedUserRepositoryInterface
{
    #[Override]
    public function getByEmailHash(string $emailHash): ?BlockedUser
    {
        return $this->findOneBy([
            'email_hash' => $emailHash
        ]);
    }

    #[Override]
    public function getByMobileHash(string $mobileHash): ?BlockedUser
    {
        return $this->findOneBy([
            'mobile_hash' => $mobileHash
        ]);
    }

    #[Override]
    public function save(BlockedUser $blockedUser): void
    {
        $em = $this->getEntityManager();

        $em->persist($blockedUser);
    }

    #[Override]
    public function prototype(): BlockedUser
    {
        return new BlockedUser();
    }
}
