<?php

declare(strict_types=1);

namespace Stu\Module\Api;

use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmation;
use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmationInterface;
use Stu\Component\Player\Deletion\Handler;
use Stu\Component\Player\Deletion\PlayerDeletion;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
use Stu\Component\Player\Invitation\InvitePlayer;
use Stu\Component\Player\Invitation\InvitePlayerInterface;
use Stu\Component\Player\Register\PlayerCreator;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Component\Player\Register\RegistrationEmailSender;
use Stu\Component\Player\Register\RegistrationEmailSenderInterface;
use Stu\Component\Player\Register\PlayerDefaultsCreator;
use Stu\Component\Player\Register\PlayerDefaultsCreatorInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    InvitePlayerInterface::class => autowire(InvitePlayer::class),
    RequestDeletionConfirmationInterface::class => autowire(RequestDeletionConfirmation::class),
    PlayerDeletionInterface::class => create(PlayerDeletion::class)->constructor(
        get(UserRepositoryInterface::class),
        [
            autowire(Handler\AllianceDeletionHandler::class),
            autowire(Handler\ColonyDeletionHandler::class),
            autowire(Handler\KnPostDeletionHandler::class),
            autowire(Handler\RpgPlotDeletionHandler::class),
            autowire(Handler\ShipDeletionHandler::class),
            autowire(Handler\FleetDeletionHandler::class),
            autowire(Handler\UserDeletionHandler::class)
        ]
    ),
    PlayerCreatorInterface::class => autowire(PlayerCreator::class),
    PlayerDefaultsCreatorInterface::class => autowire(PlayerDefaultsCreator::class),
    RegistrationEmailSenderInterface::class => autowire(RegistrationEmailSender::class),
];
