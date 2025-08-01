<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use Monolog\Logger;

enum LogLevelEnum: int
{
    case INFO = 2;
    case WARNING = 3;
    case ERROR = 7;

    public function log(string $message, Logger $logger): void
    {
        match ($this) {
            self::INFO => $logger->info($message),
            self::WARNING => $logger->warning($message),
            self::ERROR => $logger->error($message)
        };
    }
}
