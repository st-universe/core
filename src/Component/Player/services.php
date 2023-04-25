<?php

declare(strict_types=1);

namespace Stu\Module\Player;

use JBBCode\Parser;
use Stu\Component\Player\ColonizationChecker;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Component\Player\ColonyLimitCalculator;
use Stu\Component\Player\ColonyLimitCalculatorInterface;
use Stu\Component\Player\CrewLimitCalculator;
use Stu\Component\Player\CrewLimitCalculatorInterface;
use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmation;
use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmationInterface;
use Stu\Component\Player\Deletion\Handler;
use Stu\Component\Player\Deletion\PlayerDeletion;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Component\Player\PlayerRelationDeterminator;
use Stu\Component\Player\PlayerRelationDeterminatorInterface;
use Stu\Component\Player\Register\LocalPlayerCreator;
use Stu\Component\Player\Register\PlayerCreator;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Component\Player\Register\PlayerDefaultsCreator;
use Stu\Component\Player\Register\PlayerDefaultsCreatorInterface;
use Stu\Component\Player\Register\RegistrationEmailSender;
use Stu\Component\Player\Register\RegistrationEmailSenderInterface;
use Stu\Component\Player\Register\SmsVerificationCodeSender;
use Stu\Component\Player\Register\SmsVerificationCodeSenderInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    ColonyLimitCalculatorInterface::class => autowire(ColonyLimitCalculator::class),
    ColonizationCheckerInterface::class => autowire(ColonizationChecker::class),
    RequestDeletionConfirmationInterface::class => autowire(RequestDeletionConfirmation::class),
    PlayerDeletionInterface::class => create(PlayerDeletion::class)->constructor(
        get(UserRepositoryInterface::class),
        get(StuConfigInterface::class),
        get(LoggerUtilFactoryInterface::class),
        get(Parser::class),
        [
            autowire(Handler\AllianceDeletionHandler::class),
            autowire(Handler\ColonyDeletionHandler::class),
            autowire(Handler\PrivateMessageDeletionHandler::class),
            autowire(Handler\KnPostDeletionHandler::class),
            autowire(Handler\RpgPlotDeletionHandler::class),
            autowire(Handler\TradepostDeletionHandler::class),
            autowire(Handler\CrewDeletionHandler::class),
            autowire(Handler\ShipDeletionHandler::class),
            autowire(Handler\FleetDeletionHandler::class),
            autowire(Handler\ShipBuildplanDeletionHandler::class),
            autowire(Handler\UserMapDeletionHandler::class),
            autowire(Handler\UserDeletionHandler::class)
        ]
    ),
    LocalPlayerCreator::class => autowire(),
    PlayerCreatorInterface::class => autowire(PlayerCreator::class),
    PlayerDefaultsCreatorInterface::class => autowire(PlayerDefaultsCreator::class),
    RegistrationEmailSenderInterface::class => autowire(RegistrationEmailSender::class),
    SmsVerificationCodeSenderInterface::class => autowire(SmsVerificationCodeSender::class),
    PlayerRelationDeterminatorInterface::class => autowire(PlayerRelationDeterminator::class),
    CrewLimitCalculatorInterface::class => autowire(CrewLimitCalculator::class),
];
