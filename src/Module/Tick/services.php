<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use function DI\autowire;

return [
    'process_tick_handler' => [
        autowire(Process\FinishBuildJobs::class),
        autowire(Process\FinishShipBuildJobs::class),
        autowire(Process\FinishTerraformingJobs::class),
        autowire(Process\ShieldRegeneration::class),
    ],
];