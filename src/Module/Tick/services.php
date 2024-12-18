<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Psr\Container\ContainerInterface;
use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\Component\PirateFlight;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Lib\Pirate\PirateCreation;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
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
use Stu\Module\Tick\Spacecraft\ManagerComponent\AnomalyCreationCheck;
use Stu\Module\Tick\Spacecraft\ManagerComponent\AnomalyProcessing;
use Stu\Module\Tick\Spacecraft\ManagerComponent\CrewLimitations;
use Stu\Module\Tick\Spacecraft\ManagerComponent\EscapePodHandling;
use Stu\Module\Tick\Spacecraft\ManagerComponent\LowerHull;
use Stu\Module\Tick\Spacecraft\ManagerComponent\NpcShipHandling;
use Stu\Module\Tick\Spacecraft\ManagerComponent\RepairActions;
use Stu\Module\Tick\Spacecraft\SpacecraftTick;
use Stu\Module\Tick\Spacecraft\SpacecraftTickInterface;
use Stu\Module\Tick\Spacecraft\SpacecraftTickManager;
use Stu\Module\Tick\Spacecraft\SpacecraftTickManagerInterface;
use Stu\Module\Tick\Spacecraft\SpacecraftTickRunner;

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
    SpacecraftTickInterface::class => autowire(SpacecraftTick::class),
    SpacecraftTickManagerInterface::class => autowire(SpacecraftTickManager::class)
        ->constructorParameter(
            'components',
            [
                autowire(AnomalyProcessing::class),
                autowire(CrewLimitations::class),
                autowire(EscapePodHandling::class),
                autowire(RepairActions::class),
                autowire(SpacecraftTick::class),
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
    MaintenanceTickRunnerFactoryInterface::class => autowire(MaintenanceTickRunnerFactory::class)
        ->constructorParameter('handlerList', get(MaintenanceHandlerInterface::class)),
    MaintenanceTickRunner::class => fn(ContainerInterface $dic): TickRunnerInterface => $dic
        ->get(MaintenanceTickRunnerFactoryInterface::class)
        ->createMaintenanceTickRunner(),
    ProcessTickRunner::class => create(ProcessTickRunner::class)
        ->constructor(
            get(TransactionTickRunnerInterface::class),
            get(ProcessTickHandlerInterface::class)
        ),
    SpacecraftTickRunner::class => autowire(),
    PirateTickInterface::class => autowire(PirateTick::class)->constructorParameter(
        'behaviours',
        get(PirateBehaviourInterface::class)
    ),
    PirateCreationInterface::class => autowire(PirateCreation::class),
    PirateFlightInterface::class => autowire(PirateFlight::class),
    PirateTickRunner::class => autowire()
];
