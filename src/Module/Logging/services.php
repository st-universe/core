<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use function DI\autowire;

return [
    LoggerUtilFactoryInterface::class => autowire(LoggerUtilFactory::class),
    LoggerUtilInterface::class => autowire(LoggerUtil::class) //TODO kann wech!
];
