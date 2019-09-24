<?php

declare(strict_types=1);

namespace Stu\Module\Api;

use Stu\Component\Player\Register\PlayerCreator;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Component\Player\Register\Exception\RegistrationEmailSender;
use Stu\Component\Player\Register\Exception\RegistrationEmailSenderInterface;
use Stu\Component\Player\Register\PlayerDefaultsCreator;
use Stu\Component\Player\Register\PlayerDefaultsCreatorInterface;
use function DI\autowire;

return [
    PlayerCreatorInterface::class => autowire(PlayerCreator::class),
    PlayerDefaultsCreatorInterface::class => autowire(PlayerDefaultsCreator::class),
    RegistrationEmailSenderInterface::class => autowire(RegistrationEmailSender::class),
];