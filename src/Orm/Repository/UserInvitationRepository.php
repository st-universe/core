<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use DateTimeInterface;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInvitation;

/**
 * @extends EntityRepository<UserInvitation>
 */
final class UserInvitationRepository extends EntityRepository implements UserInvitationRepositoryInterface
{
    #[\Override]
    public function prototype(): UserInvitation
    {
        return new UserInvitation();
    }

    #[\Override]
    public function save(UserInvitation $userInvitation): void
    {
        $em = $this->getEntityManager();

        $em->persist($userInvitation);
        $em->flush();
    }

    #[\Override]
    public function getInvitationsByUser(User $user): array
    {
        return $this->findBy([
            'user_id' => $user
        ]);
    }

    #[\Override]
    public function getByToken(string $token): ?UserInvitation
    {
        return $this->findOneBy([
            'token' => $token
        ]);
    }

    #[\Override]
    public function truncateExpiredTokens(DateTimeInterface $ttl): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ui WHERE ui.date < :ttl AND ui.invited_user_id IS NULL',
                UserInvitation::class
            )
        )
            ->setParameters([
                'ttl' => $ttl
            ])
            ->execute();
    }
}
