<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Module\Tal\StatusBarFactory;
use Stu\Module\Tal\StatusBarFactoryInterface;

use function DI\autowire;

return [
    StatusBarFactoryInterface::class => autowire(StatusBarFactory::class),
];
