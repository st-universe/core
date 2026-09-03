<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShuttleManagement;

interface ShowShuttleManagementRequestInterface
{
    public function getShipId(): int;

    public function getColonyId(): int;
}
