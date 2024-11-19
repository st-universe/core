<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Psr\Container\ContainerInterface;
use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\Component\PirateFlight;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Lib\Pirate\PirateCreation;
use Stu\Lib\Pirate\PirateCreationInterface;
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
use Stu\Module\Tick\Pirate\PirateTick;
use Stu\Module\Tick\Pirate\PirateTickInterface;
use Stu\Module\Tick\Pirate\PirateTickRunner;
use Stu\Module\Tick\Process\FinishBuildJobs;
use Stu\Module\Tick\Process\FinishShipBuildJobs;
use Stu\Module\Tick\Process\FinishShipRetrofitJobs;
use Stu\Module\Tick\Process\FinishTerraformingJobs;
use Stu\Module\Tick\Process\FinishTholianWebs;
use Stu\Module\Tick\Process\ProcessTickHandlerInterface;
use Stu\Module\Tick\Process\ProcessTickRunner;
use Stu\Module\Tick\Process\RepairTaskJobs;
use Stu\Module\Tick\Process\ShieldRegeneration;
use Stu\Module\Tick\Ship\ManagerComponent\AnomalyCreationCheck;
use Stu\Module\Tick\Ship\ManagerComponent\AnomalyProcessing;
use Stu\Module\Tick\Ship\ManagerComponent\CrewLimitations;
use Stu\Module\Tick\Ship\ManagerComponent\EscapePodHandling;
use Stu\Module\Tick\Ship\ManagerComponent\LowerHull;
use Stu\Module\Tick\Ship\ManagerComponent\NpcShipHandling;
use Stu\Module\Tick\Ship\ManagerComponent\RepairActions;
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
    ShipTickManagerInterface::class => autowire(ShipTickManager::class)
        ->constructorParameter(
            'components',
            [
                autowire(AnomalyProcessing::class),
                autowire(CrewLimitations::class),
                autowire(EscapePodHandling::class),
                autowire(RepairActions::class),
                autowire(ShipTick::class),
                autowire(NpcShipHandling::class),
                autowire(LowerHull::class),
                autowire(AnomalyCreationCheck::class),
            ]
        ),
    TickManagerInterface::class => autowire(TickManager::class),
    LockManagerInterface::class => autowire(LockManager::class),
    ProcessTickHandlerInterface::class => [
        autowire(FinishBuildJobs::class),
        autowire(FinishShipBuildJobs::class),
        autowire(FinishShipRetrofitJobs::class),
        autowire(FinishTerraformingJobs::class),
        autowire(ShieldRegeneration::class),
        autowire(RepairTaskJobs::class),
        autowire(FinishTholianWebs::class)
    ],
    TransactionTickRunnerInterface::class => autowire(TransactionTickRunner::class),
    MaintenanceTickRunnerFactoryInterface::class => autowire(MaintenanceTickRunnerFactory::class),
    MaintenanceTickRunner::class => fn(ContainerInterface $dic): TickRunnerInterface => $dic
        ->get(MaintenanceTickRunnerFactoryInterface::class)
        ->createMaintenanceTickRunner(),
    ProcessTickRunner::class => create(ProcessTickRunner::class)
        ->constructor(
            get(TransactionTickRunnerInterface::class),
            get(ProcessTickHandlerInterface::class)
        ),
    ShipTickRunner::class => autowire(),
    PirateTickInterface::class => autowire(PirateTick::class)->constructorParameter(
        'behaviours',
        get(PirateBehaviourInterface::class)
    ),
    PirateCreationInterface::class => autowire(PirateCreation::class),
    PirateFlightInterface::class => autowire(PirateFlight::class),
    PirateTickRunner::class => autowire()
];
