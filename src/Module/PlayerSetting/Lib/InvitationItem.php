<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Lib;

use Noodlehaus\ConfigInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitationInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class InvitationItem implements InvitationItemInterface
{
    private ConfigInterface $config;

    private UserInvitationInterface $userInvitation;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ConfigInterface $config,
        UserInvitationInterface $userInvitation,
        UserRepositoryInterface $userRepository
    ) {
        $this->userInvitation = $userInvitation;
        $this->config = $config;
        $this->userRepository = $userRepository;
    }

    public function getLink(): string
    {
        return sprintf(
            '%s/?SHOW_REGISTRATION=1&token=%s',
            $this->config->get('game.base_url'),
            $this->userInvitation->getToken()
        );
    }

    public function getInvitedUser(): ?UserInterface
    {
        return $this->userInvitation->getInvitedUserId()
            ? $this->userRepository->find($this->userInvitation->getInvitedUserId()) : null;
    }

    public function getDate(): int
    {
        return $this->userInvitation->getDate()->getTimestamp();
    }

    public function isValid(): bool
    {
        return $this->userInvitation->isValid(
            $this->config->get('game.invitation.ttl')
        );
    }
}
