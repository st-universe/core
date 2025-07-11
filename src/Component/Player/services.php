<?php

declare(strict_types=1);

namespace Stu\Module\Player;

use Stu\Component\Player\ColonizationChecker;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Component\Player\ColonyLimitCalculator;
use Stu\Component\Player\ColonyLimitCalculatorInterface;
use Stu\Component\Player\CrewLimitCalculator;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmation;
use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmationInterface;
use Stu\Component\Player\Deletion\Handler\AllianceDeletionHandler;
use Stu\Component\Player\Deletion\Handler\AstronomicalEntryDeletionHandler;
use Stu\Component\Player\Deletion\Handler\ColonyDeletionHandler;
use Stu\Component\Player\Deletion\Handler\CrewDeletionHandler;
use Stu\Component\Player\Deletion\Handler\DockingPrivilegeDeletionHandler;
use Stu\Component\Player\Deletion\Handler\FleetDeletionHandler;
use Stu\Component\Player\Deletion\Handler\ForeignCrewDumpingHandler;
use Stu\Component\Player\Deletion\Handler\KnPostDeletionHandler;
use Stu\Component\Player\Deletion\Handler\PrivateMessageDeletionHandler;
use Stu\Component\Player\Deletion\Handler\PirateWrathDeletionHandler;
use Stu\Component\Player\Deletion\Handler\RpgPlotDeletionHandler;
use Stu\Component\Player\Deletion\Handler\SpacecraftBuildplanDeletionHandler;
use Stu\Component\Player\Deletion\Handler\SpacecraftDeletionHandler;
use Stu\Component\Player\Deletion\Handler\TradepostDeletionHandler;
use Stu\Component\Player\Deletion\Handler\UserDeletionHandler;
use Stu\Component\Player\Deletion\Handler\RefererDeletionHandler;
use Stu\Component\Player\Deletion\Handler\ShipTakeoverHandler;
use Stu\Component\Player\Deletion\Handler\UserMapDeletionHandler;
use Stu\Component\Player\Deletion\PlayerDeletion;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Component\Player\Register\LocalPlayerCreator;
use Stu\Component\Player\Register\PlayerCreator;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Component\Player\Register\PlayerDefaultsCreator;
use Stu\Component\Player\Register\PlayerDefaultsCreatorInterface;
use Stu\Component\Player\Register\RegistrationEmailSender;
use Stu\Component\Player\Register\RegistrationEmailSenderInterface;
use Stu\Component\Player\Register\SmsVerificationCodeSender;
use Stu\Component\Player\Register\SmsVerificationCodeSenderInterface;
use Stu\Component\Player\Relation\EnemyDeterminator;
use Stu\Component\Player\Relation\FriendDeterminator;
use Stu\Component\Player\Relation\PlayerRelationDeterminator;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Player\Settings\UserSettingsProvider;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;

use function DI\autowire;

return [
    ColonyLimitCalculatorInterface::class => autowire(ColonyLimitCalculator::class),
    ColonizationCheckerInterface::class => autowire(ColonizationChecker::class),
    RequestDeletionConfirmationInterface::class => autowire(RequestDeletionConfirmation::class),
    PlayerDeletionInterface::class => autowire(PlayerDeletion::class)->constructorParameter(
        'deletionHandlers',
        [
            autowire(PirateWrathDeletionHandler::class),
            autowire(AllianceDeletionHandler::class),
            autowire(ColonyDeletionHandler::class),
            autowire(PrivateMessageDeletionHandler::class),
            autowire(KnPostDeletionHandler::class),
            autowire(RpgPlotDeletionHandler::class),
            autowire(TradepostDeletionHandler::class),
            autowire(CrewDeletionHandler::class),
            autowire(ForeignCrewDumpingHandler::class),
            autowire(ShipTakeoverHandler::class),
            autowire(SpacecraftDeletionHandler::class),
            autowire(AstronomicalEntryDeletionHandler::class),
            autowire(FleetDeletionHandler::class),
            autowire(SpacecraftBuildplanDeletionHandler::class),
            autowire(RefererDeletionHandler::class),
            autowire(DockingPrivilegeDeletionHandler::class),
            autowire(UserMapDeletionHandler::class),
            autowire(UserDeletionHandler::class)
        ]
    ),
    LocalPlayerCreator::class => autowire(),
    PlayerCreatorInterface::class => autowire(PlayerCreator::class),
    PlayerDefaultsCreatorInterface::class => autowire(PlayerDefaultsCreator::class),
    RegistrationEmailSenderInterface::class => autowire(RegistrationEmailSender::class),
    SmsVerificationCodeSenderInterface::class => autowire(SmsVerificationCodeSender::class),
    PlayerRelationDeterminatorInterface::class => autowire(PlayerRelationDeterminator::class)
        ->constructorParameter(
            'friendDeterminator',
            autowire(FriendDeterminator::class)
        )
        ->constructorParameter(
            'enemyDeterminator',
            autowire(EnemyDeterminator::class)
        ),
    CrewLimitCalculatorInterface::class => autowire(CrewLimitCalculator::class),
    UserSettingsProviderInterface::class => autowire(UserSettingsProvider::class)
];
