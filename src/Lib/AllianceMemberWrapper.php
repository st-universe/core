<?php

namespace Lib;

use Stu\Orm\Entity\AllianceInterface;
use UserData;

class AllianceMemberWrapper
{

    private $user = null;
    private $alliance = null;

    function __construct(UserData $user, AllianceInterface $alliance)
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
}