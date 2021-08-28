<?php

declare(strict_types=1);

namespace Stu\Component\Player\Invitation;

use DateTime;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitationInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;

final class InvitePlayer implements InvitePlayerInterface
{
    private UserInvitationRepositoryInterface $userInvitationRepository;

    public function __construct(
        UserInvitationRepositoryInterface $userInvitationRepository
    ) {
        $this->userInvitationRepository = $userInvitationRepository;
    }

    public function invite(
        UserInterface $user
    ): UserInvitationInterface {
        $token = bin2hex(random_bytes(16));

        $invitation = $this->userInvitationRepository
            ->prototype()
            ->setUserId($user->getId())
            ->setDate(new DateTime())
            ->setToken($token);

        $this->userInvitationRepository->save($invitation);

        return $invitation;
    }
}
