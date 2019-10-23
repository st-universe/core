<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitation;
use Stu\Orm\Entity\UserInvitationInterface;

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
        $em->flush($userInvitation);
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
}
