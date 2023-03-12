<?php

namespace Stu\Module\Station\View\ShowShuttleManagement;

interface ShowShuttleManagementRequestInterface
{
    public function getShipId(): int;

    public function getStationId(): int;
}
