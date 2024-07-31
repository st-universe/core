<?php

declare(strict_types=1);

namespace Stu\Module\Cli;

use Stu\Component\Cli\UserCreateCommand;

use function DI\autowire;
use function DI\get;

return [
    UserCreateCommand::class => autowire(),
];
