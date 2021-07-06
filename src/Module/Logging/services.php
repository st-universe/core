<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use function DI\autowire;

return [
    LoggerUtilInterface::class => autowire(LoggerUtil::class)
];
