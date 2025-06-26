<?php

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

final class AllianceMemberWrapper
{
    public function __construct(private User $user, private Alliance $alliance)
    {
    }

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
        return $this->user->getId() === $this->alliance->getFounder()->getUserId();
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
