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
use Stu\Module\Tick\Pirate\Behaviour\AttackShipBehaviour;
use Stu\Module\Tick\Pirate\Behaviour\FlyBehaviour;
use Stu\Module\Tick\Pirate\Behaviour\HideBehaviour;
use Stu\Module\Tick\Pirate\Behaviour\RubColonyBehaviour;
use Stu\Module\Tick\Pirate\Component\PirateFlight;
use Stu\Module\Tick\Pirate\Component\PirateFlightInterface;
use Stu\Module\Tick\Pirate\PirateBehaviourEnum;
use Stu\Module\Tick\Pirate\PirateCreation;
use Stu\Module\Tick\Pirate\PirateCreationInterface;
use Stu\Module\Tick\Pirate\PirateTick;
use Stu\Module\Tick\Pirate\PirateTickInterface;
use Stu\Module\Tick\Pirate\PirateTickRunner;
use Stu\Module\Tick\Process\ProcessTickRunner;
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
    ShipTickRunner::class => autowire(),
    PirateTickInterface::class => autowire(PirateTick::class)->constructorParameter(
        'behaviours',
        [
            PirateBehaviourEnum::FLY->value => autowire(FlyBehaviour::class),
            PirateBehaviourEnum::RUB_COLONY->value => autowire(RubColonyBehaviour::class),
            PirateBehaviourEnum::ATTACK_SHIP->value => autowire(AttackShipBehaviour::class),
            PirateBehaviourEnum::HIDE->value => autowire(HideBehaviour::class)
        ]
    ),
    PirateCreationInterface::class => autowire(PirateCreation::class),
    PirateFlightInterface::class => autowire(PirateFlight::class),
    PirateTickRunner::class => autowire(),
];
