<?php

declare(strict_types=1);

namespace Stu\Component\Logging;

use function DI\autowire;

return [
    LoggerUtilInterface::class => autowire(LoggerUtil::class)
];
