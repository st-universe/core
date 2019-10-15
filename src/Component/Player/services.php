<?php

declare(strict_types=1);

namespace Stu\Module\Api;

use Stu\Component\Player\Deletion\Handler;
use Stu\Component\Player\Deletion\PlayerDeletion;
use Stu\Component\Player\Deletion\PlayerDeletionInterface;
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
    PlayerDeletionInterface::class => create(PlayerDeletion::class)->constructor(
        get(UserRepositoryInterface::class),
        [
            autowire(Handler\AllianceDeletionHandler::class),
            autowire(Handler\ColonyDeletionHandler::class),
            autowire(Handler\ContactlistDeletionHandler::class),
            autowire(Handler\CrewDeletionHandler::class),
            autowire(Handler\DatabaseDeletionHandler::class),
            autowire(Handler\IgnorelistDeletionHandler::class),
            autowire(Handler\KnPostingDeletionHandler::class),
            autowire(Handler\KnCommentDeletionHandler::class),
            autowire(Handler\NotesDeletionHandler::class),
            autowire(Handler\RpgPlotDeletionHandler::class),
            autowire(Handler\PmCategoryDeletionHandler::class),
            autowire(Handler\ResearchDeletionHandler::class),
            autowire(Handler\ShipDeletionHandler::class),
            autowire(Handler\BuildplanDeletionHandler::class),
            autowire(Handler\FleetDeletionHandler::class),
            autowire(Handler\TradeDeletionHandler::class),
            autowire(Handler\UserDeletionHandler::class)
        ]
    ),
    PlayerCreatorInterface::class => autowire(PlayerCreator::class),
    PlayerDefaultsCreatorInterface::class => autowire(PlayerDefaultsCreator::class),
    RegistrationEmailSenderInterface::class => autowire(RegistrationEmailSender::class),
];
