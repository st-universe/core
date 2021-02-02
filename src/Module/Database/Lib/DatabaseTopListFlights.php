<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

class DatabaseTopListFlights extends DatabaseTopList
{

    private $signatures = null;
    private $faction = null;

    function __construct($entry)
    {
        parent::__construct($entry['user_id']);
        $this->signatures = $entry['sc'];
        $this->faction = $entry['race'];
    }

    function getSignatures()
    {
        return $this->signatures;
    }

    function getFaction()
    {
        return $this->faction;
    }
}
