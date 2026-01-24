<?php

namespace Stu\Module\Alliance\Lib;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

final class AllianceMemberWrapper
{
    public function __construct(
        private User $user,
        private Alliance $alliance,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAlliance(): Alliance
    {
        return $this->alliance;
    }

    public function isFounder(): bool
    {
        return $this->allianceJobManager->hasUserPermission($this->user, $this->alliance, AllianceJobPermissionEnum::FOUNDER);
    }

    public function getUserId(): int
    {
        return $this->user->getId();
    }

    public function getOnlineStateCssClass(): string
    {
        if ($this->user->isOnline()) {
            return 'online';
        }

        return 'offline';
    }
}
