<?php

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;

final class AllianceMemberWrapper
{
    public function __construct(private UserInterface $user, private AllianceInterface $alliance)
    {
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    public function isFounder(): bool
    {
        return $this->getUser()->getId() === $this->getAlliance()->getFounder()->getUserId();
    }

    public function getUserId(): int
    {
        return $this->getUser()->getId();
    }

    public function getOnlineStateCssClass(): string
    {
        if ($this->user->isOnline()) {
            return 'online';
        }

        return 'offline';
    }
}
