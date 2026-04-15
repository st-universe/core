<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Psr\Container\ContainerInterface;
use Stu\Component\Map\Effects\EffectHandlingInterface;
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
use Stu\Module\Tick\Colony\Component\ProceedMigration;
use Stu\Module\Tick\Colony\Component\ProceedStorage;
use Stu\Module\Tick\History\Component\EventMapGeneration;
use Stu\Module\Tick\History\Component\IonStormMapGeneration;
use Stu\Module\Tick\History\HistoryTickRunner;
use Stu\Module\Tick\Lock\LockManager;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunner;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunnerFactory;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunnerFactoryInterface;
use Stu\Module\Tick\Pirate\PirateTick;
use Stu\Module\Tick\Pirate\PirateTickInterface;
use Stu\Module\Tick\Pirate\PirateTickRunner;
use Stu\Module\Tick\Process\EndPirateRound;
use Stu\Module\Tick\Process\FinishBuildJobs;
use Stu\Module\Tick\Process\FinishShipBuildJobs;
use Stu\Module\Tick\Process\FinishShipRetrofitJobs;
use Stu\Module\Tick\Process\FinishTerraformingJobs;
use Stu\Module\Tick\Process\FinishTholianWebs;
use Stu\Module\Tick\Process\NewDealsInformation;
use Stu\Module\Tick\Process\ProcessTickHandlerInterface;
use Stu\Module\Tick\Process\ProcessTickRunner;
use Stu\Module\Tick\Process\RepairTaskJobs;
use Stu\Module\Tick\Process\ShieldRegeneration;
use Stu\Module\Tick\Spacecraft\Handler\AggregationSystemHandler;
use Stu\Module\Tick\Spacecraft\Handler\BussardCollectorHandler;
use Stu\Module\Tick\Spacecraft\Handler\EnergyConsumeHandler;
use Stu\Module\Tick\Spacecraft\Handler\EpsSystemCheckHandler;
use Stu\Module\Tick\Spacecraft\Handler\FinishedAstroMappingHandler;
use Stu\Module\Tick\Spacecraft\Handler\FinishedTakeoverHandler;
use Stu\Module\Tick\Spacecraft\Handler\LifeSupportCheckHandler;
use Stu\Module\Tick\Spacecraft\Handler\StationConstructionHandler;
use Stu\Module\Tick\Spacecraft\Handler\StationPassiveRepairHandler;
use Stu\Module\Tick\Spacecraft\Handler\SystemDeactivationHandler;
use Stu\Module\Tick\Spacecraft\Handler\TrackerDeviceHandler;
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
            [
                autowire(AdvanceResearch::class),
                autowire(ProceedStorage::class),
                autowire(ProceedMigration::class)
            ]
        ),
    ColonyTickManagerInterface::class => autowire(ColonyTickManager::class)->lazy(),
    StationConstructionHandler::class => autowire(StationConstructionHandler::class),
    StationPassiveRepairHandler::class => autowire(StationPassiveRepairHandler::class),
    SpacecraftTickInterface::class => autowire(SpacecraftTick::class)
        ->constructorParameter(
            'handlers',
            [
                get(EffectHandlingInterface::class),
                get(StationConstructionHandler::class),
                get(StationPassiveRepairHandler::class),
                autowire(LifeSupportCheckHandler::class),
                autowire(EpsSystemCheckHandler::class),
                autowire(SystemDeactivationHandler::class),
                autowire(EnergyConsumeHandler::class),
                autowire(FinishedTakeoverHandler::class),
                autowire(FinishedAstroMappingHandler::class),
                autowire(TrackerDeviceHandler::class),
                autowire(BussardCollectorHandler::class),
                autowire(AggregationSystemHandler::class),
            ]
        ),
    SpacecraftTickManagerInterface::class => autowire(SpacecraftTickManager::class)
        ->lazy()
        ->constructorParameter(
            'components',
            [
                autowire(AnomalyProcessing::class),
                autowire(CrewLimitations::class),
                autowire(EscapePodHandling::class),
                autowire(RepairActions::class),
                get(SpacecraftTickInterface::class),
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
        autowire(FinishTholianWebs::class),
        autowire(EndPirateRound::class),
        autowire(NewDealsInformation::class)
    ],
    TransactionTickRunnerInterface::class => autowire(TransactionTickRunner::class),
    MaintenanceTickRunnerFactoryInterface::class => autowire(MaintenanceTickRunnerFactory::class)
        ->constructorParameter('handlerList', get(MaintenanceHandlerInterface::class)),
    MaintenanceTickRunner::class => fn (ContainerInterface $dic): TickRunnerInterface => $dic
        ->get(MaintenanceTickRunnerFactoryInterface::class)
        ->createMaintenanceTickRunner(),
    ProcessTickRunner::class => create(ProcessTickRunner::class)
        ->constructor(
            get(TransactionTickRunnerInterface::class),
            get(ProcessTickHandlerInterface::class)
        ),
    SpacecraftTickRunner::class => autowire(),
    PirateTickInterface::class => autowire(PirateTick::class)->lazy()->constructorParameter(
        'behaviours',
        get(PirateBehaviourInterface::class)
    ),
    PirateCreationInterface::class => autowire(PirateCreation::class),
    PirateFlightInterface::class => autowire(PirateFlight::class),
    PirateTickRunner::class => autowire(),
    HistoryTickRunner::class => autowire()->lazy()->constructorParameter(
        'handlerList',
        [
            autowire(EventMapGeneration::class),
            autowire(IonStormMapGeneration::class)
        ]
    ),
];
