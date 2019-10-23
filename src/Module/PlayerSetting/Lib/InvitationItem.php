<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Lib;

use Noodlehaus\ConfigInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitationInterface;

final class InvitationItem implements InvitationItemInterface
{
    private $config;

    private $userInvitation;

    public function __construct(
        ConfigInterface $config,
        UserInvitationInterface $userInvitation
    ) {
        $this->userInvitation = $userInvitation;
        $this->config = $config;
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
        return $this->userInvitation->getInvitedUser();
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
