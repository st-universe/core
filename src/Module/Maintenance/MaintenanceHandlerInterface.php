<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

interface MaintenanceHandlerInterface
{
    public function handle(): void;
}
