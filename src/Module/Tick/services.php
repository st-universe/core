<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Stu\Module\Tick\Colony\ColonyTick;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Colony\ColonyTickManager;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use Stu\Module\Tick\Ship\ShipTick;
use Stu\Module\Tick\Ship\ShipTickInterface;
use Stu\Module\Tick\Ship\ShipTickManager;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;
use function DI\autowire;

return [
    ColonyTickInterface::class => autowire(ColonyTick::class),
    ColonyTickManagerInterface::class => autowire(ColonyTickManager::class),
    ShipTickInterface::class => autowire(ShipTick::class),
    ShipTickManagerInterface::class => autowire(ShipTickManager::class),
    TickManagerInterface::class => autowire(TickManager::class),
    'process_tick_handler' => [
        autowire(Process\FinishBuildJobs::class),
        autowire(Process\FinishShipBuildJobs::class),
        autowire(Process\FinishTerraformingJobs::class),
        autowire(Process\ShieldRegeneration::class),
    ],
];