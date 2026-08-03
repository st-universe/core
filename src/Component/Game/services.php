<?php

declare(strict_types=1);

namespace Stu\Component\Game;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Alliance\Event\DiplomaticRelationProposedEvent;
use Stu\Component\Alliance\Event\Listener\DiplomaticRelationProposalCreationSubscriber;
use Stu\Component\Alliance\Event\WarDeclaredEvent;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\Event\Listener\CrewExperienceSubscriber;
use Stu\Component\History\Event\HistoryEntrySubscriber;
use Stu\Component\Spacecraft\Event\Listener\WarpdriveActivationSubscriber;
use Stu\Component\Spacecraft\Event\WarpdriveActivationEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

return [
    EventDispatcherInterface::class => function (ContainerInterface $c): EventDispatcherInterface {
        $eventDispatcher = new EventDispatcher();

        $eventDispatcher->addListener(
            WarDeclaredEvent::class,
            static function (WarDeclaredEvent $event) use ($c): void {
                $c->get(DiplomaticRelationProposalCreationSubscriber::class)->onWarDeclaration($event);
            }
        );
        $eventDispatcher->addListener(
            WarDeclaredEvent::class,
            static function (WarDeclaredEvent $event) use ($c): void {
                $c->get(HistoryEntrySubscriber::class)->onWarDeclaration($event);
            }
        );
        $eventDispatcher->addListener(
            DiplomaticRelationProposedEvent::class,
            static function (DiplomaticRelationProposedEvent $event) use ($c): void {
                $c->get(DiplomaticRelationProposalCreationSubscriber::class)->onRelationProposal($event);
            }
        );
        $eventDispatcher->addListener(
            WarpdriveActivationEvent::class,
            static function (WarpdriveActivationEvent $event) use ($c): void {
                $c->get(WarpdriveActivationSubscriber::class)->onWarpdriveActivation($event);
            }
        );
        $eventDispatcher->addListener(
            CrewExperienceEvent::class,
            static function (CrewExperienceEvent $event) use ($c): void {
                $c->get(CrewExperienceSubscriber::class)->onCrewExperience($event);
            }
        );

        return $eventDispatcher;
    },
];
