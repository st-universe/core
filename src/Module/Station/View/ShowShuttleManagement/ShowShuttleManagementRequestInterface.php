<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowShuttleManagement;

interface ShowShuttleManagementRequestInterface
{
    public function getShipId(): int;

    public function getStationId(): int;
}
