<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Stu\Module\Tick\Colony\ColonyTick;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Colony\ColonyTickManager;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use function DI\autowire;

return [
    ColonyTickInterface::class => autowire(ColonyTick::class),
    ColonyTickManagerInterface::class => autowire(ColonyTickManager::class),
    'process_tick_handler' => [
        autowire(Process\FinishBuildJobs::class),
        autowire(Process\FinishShipBuildJobs::class),
        autowire(Process\FinishTerraformingJobs::class),
        autowire(Process\ShieldRegeneration::class),
    ],
];