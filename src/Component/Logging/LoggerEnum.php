<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

final class LoggerEnum
{
    // log levels
    public const LEVEL_INFO = 2;
    public const LEVEL_WARNING = 3;
    public const LEVEL_ERROR = 7;

    public const LEVEL_METHODS = [
        self::LEVEL_INFO => 'info',
        self::LEVEL_WARNING => 'warning',
        self::LEVEL_ERROR => 'error'
    ];
}
