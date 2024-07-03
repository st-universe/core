<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

final class LoggerEnum
{
    // log levels
    public const int LEVEL_INFO = 2;
    public const int LEVEL_WARNING = 3;
    public const int LEVEL_ERROR = 7;

    public const array LEVEL_METHODS = [
        self::LEVEL_INFO => 'info',
        self::LEVEL_WARNING => 'warning',
        self::LEVEL_ERROR => 'error'
    ];
}
