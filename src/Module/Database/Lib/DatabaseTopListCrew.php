<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

class DatabaseTopListCrew extends DatabaseTopList
{

    private $crewcount = null;
    private $faction = null;

    function __construct($entry)
    {
        parent::__construct($entry['user_id']);
        $this->crewcount = $entry['crewc'];
        $this->faction = $entry['race'];
    }

    function getCrewCount()
    {
        return $this->crewcount;
    }

    function getFaction()
    {
        return $this->faction;
    }
}
