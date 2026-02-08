<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use function DI\autowire;

return [
    UserCreateCommand::class => autowire()
];
