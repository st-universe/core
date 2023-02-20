<?php

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;

final class AllianceMemberWrapper
{
    private UserInterface $user;

    private AllianceInterface $alliance;

    function __construct(UserInterface $user, AllianceInterface $alliance)
    {
        $this->user = $user;
        $this->alliance = $alliance;
    }

    function getUser(): UserInterface
    {
        return $this->user;
    }

    function getAlliance(): AllianceInterface
    {
        return $this->alliance;
    }

    function isFounder(): bool
    {
        return $this->getUser()->getId() === $this->getAlliance()->getFounder()->getUserId();
    }

    function getUserId(): int
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
