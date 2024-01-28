<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Stu\Module\Tick\Process\FinishBuildJobs;
use Stu\Module\Tick\Process\FinishShipBuildJobs;
use Stu\Module\Tick\Process\FinishTerraformingJobs;
use Stu\Module\Tick\Process\ShieldRegeneration;
use Stu\Module\Tick\Process\RepairTaskJobs;
use Stu\Module\Tick\Process\FinishTholianWebs;
use Psr\Container\ContainerInterface;
use Stu\Module\Tick\Colony\ColonyTick;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Colony\ColonyTickManager;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use Stu\Module\Tick\Colony\Component\AdvanceResearch;
use Stu\Module\Tick\Lock\LockManager;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunnerFactory;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunnerFactoryInterface;
use Stu\Module\Tick\Process\ProcessTickRunner;
use Stu\Module\Tick\Ship\ManagerComponent\AnomalyCreationCheck;
use Stu\Module\Tick\Ship\ManagerComponent\AnomalyProcessing;
use Stu\Module\Tick\Ship\ManagerComponent\CrewLimitations;
use Stu\Module\Tick\Ship\Repair\RepairActions;
use Stu\Module\Tick\Ship\Repair\RepairActionsInterface;
use Stu\Module\Tick\Ship\ShipTick;
use Stu\Module\Tick\Ship\ShipTickInterface;
use Stu\Module\Tick\Ship\ShipTickManager;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;
use Stu\Module\Tick\Ship\ShipTickRunner;

use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    ColonyTickInterface::class => autowire(ColonyTick::class)
        ->constructorParameter(
            'components',
            [autowire(AdvanceResearch::class)]
        ),
    ColonyTickManagerInterface::class => autowire(ColonyTickManager::class),
    ShipTickInterface::class => autowire(ShipTick::class),
    RepairActionsInterface::class => autowire(RepairActions::class),
    ShipTickManagerInterface::class => autowire(ShipTickManager::class)
        ->constructorParameter(
            'components',
            [
                autowire(AnomalyProcessing::class),
                autowire(CrewLimitations::class),
                autowire(AnomalyCreationCheck::class),
            ]
        ),
    TickManagerInterface::class => autowire(TickManager::class),
    LockManagerInterface::class => autowire(LockManager::class),
    'process_tick_handler' => [
        autowire(FinishBuildJobs::class),
        autowire(FinishShipBuildJobs::class),
        autowire(FinishTerraformingJobs::class),
        autowire(ShieldRegeneration::class),
        autowire(RepairTaskJobs::class),
        autowire(FinishTholianWebs::class)
    ],
    TransactionTickRunnerInterface::class => autowire(TransactionTickRunner::class),
    MaintenanceTickRunnerFactoryInterface::class => autowire(MaintenanceTickRunnerFactory::class),
    MaintenanceTickRunner::class => fn (ContainerInterface $dic): TickRunnerInterface => $dic
        ->get(MaintenanceTickRunnerFactoryInterface::class)
        ->createMaintenanceTickRunner($dic->get('maintenance_handler')),
    ProcessTickRunner::class => create(ProcessTickRunner::class)
        ->constructor(
            get(TransactionTickRunnerInterface::class),
            get('process_tick_handler')
        ),
    ShipTickRunner::class => autowire()
];
