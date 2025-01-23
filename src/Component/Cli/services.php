<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Stu\Component\Cli\UserCreateCommand;

use function DI\autowire;

return [
    UserCreateCommand::class => autowire()
];
