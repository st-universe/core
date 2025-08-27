<?php

declare(strict_types=1);

namespace Stu\Tick;

use Stu\ActionTestCase;
use Stu\Config\Init;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Maintenance\DatabaseBackup;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;

class MaintenanceTickTest extends ActionTestCase
{
    public function testExecution(): void
    {
        $handlers = Init::getContainer()
            ->get(MaintenanceHandlerInterface::class);

        foreach ($handlers as $handler) {
            if (!$handler instanceof DatabaseBackup) {
                $handler->handle();
            }
        }

        StuLogger::log('MAINTENANCE TICK TEST EXECUTED');
    }
}
