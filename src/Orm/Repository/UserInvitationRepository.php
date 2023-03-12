<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use DateTimeInterface;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitation;
use Stu\Orm\Entity\UserInvitationInterface;

/**
 * @extends EntityRepository<UserInvitation>
 */
final class UserInvitationRepository extends EntityRepository implements UserInvitationRepositoryInterface
{
    public function prototype(): UserInvitationInterface
    {
        return new UserInvitation();
    }

    public function save(UserInvitationInterface $userInvitation): void
    {
        $em = $this->getEntityManager();

        $em->persist($userInvitation);
        $em->flush();
    }

    public function getInvitationsByUser(UserInterface $user): array
    {
        return $this->findBy([
            'user_id' => $user
        ]);
    }

    public function getByToken(string $token): ?UserInvitationInterface
    {
        return $this->findOneBy([
            'token' => $token
        ]);
    }

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
