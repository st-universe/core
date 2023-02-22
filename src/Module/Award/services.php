<?php

declare(strict_types=1);

namespace Stu\Module\Award;

use Stu\Module\Award\Lib\CreateUserAward;
use Stu\Module\Award\Lib\CreateUserAwardInterface;

use function DI\autowire;

return [
    CreateUserAwardInterface::class => autowire(CreateUserAward::class),
    'DATABASE_ACTIONS' => [],
    'DATABASE_VIEWS' => [],
];
