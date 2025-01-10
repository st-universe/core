<?php

declare(strict_types=1);

namespace Stu\Lib;

use Psr\Container\ContainerInterface;
use Stu\Lib\Pirate\Behaviour\AssaultPhalanxBehaviour;
use Stu\Lib\Pirate\Behaviour\AttackShipBehaviour;
use Stu\Lib\Pirate\Behaviour\CallForSupportBehaviour;
use Stu\Lib\Pirate\Behaviour\ChangeAlertStateToRed;
use Stu\Lib\Pirate\Behaviour\DeactivateShieldsBehaviour;
use Stu\Lib\Pirate\Behaviour\FlyBehaviour;
use Stu\Lib\Pirate\Behaviour\HideBehaviour;
use Stu\Lib\Pirate\Behaviour\PirateBehaviourInterface;
use Stu\Lib\Pirate\Behaviour\RageBehaviour;
use Stu\Lib\Pirate\Behaviour\RubColonyBehaviour;
use Stu\Lib\Pirate\Behaviour\SearchFriendBehaviour;
use Stu\Lib\Pirate\Component\MoveOnLayer;
use Stu\Lib\Pirate\Component\MoveOnLayerInterface;
use Stu\Lib\Pirate\Component\PirateAttack;
use Stu\Lib\Pirate\Component\PirateAttackInterface;
use Stu\Lib\Pirate\Component\PirateFlight;
use Stu\Lib\Pirate\Component\PirateFlightInterface;
use Stu\Lib\Pirate\Component\PirateNavigation;
use Stu\Lib\Pirate\Component\PirateNavigationInterface;
use Stu\Lib\Pirate\Component\PirateWrathManager;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Lib\Pirate\Component\ReloadMinimalEps;
use Stu\Lib\Pirate\Component\ReloadMinimalEpsInterface;
use Stu\Lib\Pirate\Component\SafeFlightRoute;
use Stu\Lib\Pirate\Component\SafeFlightRouteInterface;
use Stu\Lib\Pirate\Component\TrapDetection;
use Stu\Lib\Pirate\Component\TrapDetectionInterface;
use Stu\Lib\Pirate\PirateBehaviourEnum;
use Stu\Lib\Pirate\PirateCreation;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Lib\Pirate\PirateReaction;
use Stu\Lib\Pirate\PirateReactionInterface;

use function DI\autowire;
use function DI\get;

return [
    PirateBehaviourInterface::class => [
        PirateBehaviourEnum::FLY->value => autowire(FlyBehaviour::class),
        PirateBehaviourEnum::RUB_COLONY->value => autowire(RubColonyBehaviour::class),
        PirateBehaviourEnum::ATTACK_SHIP->value => autowire(AttackShipBehaviour::class),
        PirateBehaviourEnum::HIDE->value => autowire(HideBehaviour::class),
        PirateBehaviourEnum::RAGE->value => autowire(RageBehaviour::class),
        PirateBehaviourEnum::GO_ALERT_RED->value => autowire(ChangeAlertStateToRed::class),
        PirateBehaviourEnum::CALL_FOR_SUPPORT->value => autowire(CallForSupportBehaviour::class),
        PirateBehaviourEnum::SEARCH_FRIEND->value => autowire(SearchFriendBehaviour::class),
        PirateBehaviourEnum::DEACTIVATE_SHIELDS->value => autowire(DeactivateShieldsBehaviour::class),
        PirateBehaviourEnum::ASSAULT_PHALANX->value => autowire(AssaultPhalanxBehaviour::class)
    ],
    PirateCreationInterface::class => autowire(PirateCreation::class),
    PirateReactionInterface::class => autowire(PirateReaction::class)->constructorParameter(
        'behaviours',
        get(PirateBehaviourInterface::class)
    ),
    PirateFlightInterface::class => autowire(PirateFlight::class),
    SafeFlightRouteInterface::class => autowire(SafeFlightRoute::class),
    MoveOnLayerInterface::class => autowire(MoveOnLayer::class),
    PirateNavigationInterface::class => autowire(PirateNavigation::class),
    ReloadMinimalEpsInterface::class => autowire(ReloadMinimalEps::class),
    PirateWrathManagerInterface::class => autowire(PirateWrathManager::class),
    PirateAttackInterface::class => autowire(PirateAttack::class),
    TrapDetectionInterface::class => autowire(TrapDetection::class)
];
