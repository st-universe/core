<?php

namespace Lib\Alliance;

use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;

class AllianceMemberWrapper
{

    private $user = null;
    private $alliance = null;

    function __construct(UserInterface $user, AllianceInterface $alliance)
    {
        $this->user = $user;
        $this->alliance = $alliance;
    }

    function getUser()
    {
        return $this->user;
    }

    function getAlliance()
    {
        return $this->alliance;
    }

    function isFounder()
    {
        return $this->getUser()->getId() == $this->getAlliance()->getFounder()->getUserId();
    }

    function getUserId()
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
