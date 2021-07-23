<?php

namespace Stu\Module\Colony\View\ShowShuttleManagement;

interface ShowShuttleManagementRequestInterface
{
    public function getShipId(): int;

    public function getColonyId(): int;
}
