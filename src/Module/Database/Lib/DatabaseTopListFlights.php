<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

class DatabaseTopListFlights extends DatabaseTopList
{

    private $signatures = null;
    private $shipcount = null;
    private $faction = null;

    function __construct($entry)
    {
        parent::__construct($entry['user_id']);
        $this->signatures = $entry['sc'];
        $this->shipcount = $entry['shipc'];
        $this->faction = $entry['race'];
    }

    function getSignatures()
    {
        return $this->signatures;
    }

    function getShipCount()
    {
        return $this->shipcount;
    }

    function getFaction()
    {
        return $this->faction;
    }
}
