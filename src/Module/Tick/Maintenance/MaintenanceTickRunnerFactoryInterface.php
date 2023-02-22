<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Maintenance;

use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TickRunnerInterface;

interface MaintenanceTickRunnerFactoryInterface
{
    /**
     * @param array<MaintenanceHandlerInterface> $handlerList
     */
    public function createMaintenanceTickRunner(
        array $handlerList
    ): TickRunnerInterface;
}
