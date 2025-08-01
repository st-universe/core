<?php

namespace Stu\Module\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Stu\Config\Init;
use Stu\Module\Config\StuConfigInterface;

class StuLogger
{
    private static ?Logger $logger = null;

    public static function log(string $message): void
    {
        LogLevelEnum::INFO->log($message, self::getLogger());
    }

    /** @param string|int|float $args */
    public static function logf(string $information, ...$args): void
    {
        self::log(vsprintf(
            $information,
            $args
        ));
    }

    private static function getLogger(): Logger
    {
        if (self::$logger === null) {
            self::$logger = new Logger('stu');
            self::$logger->pushHandler(
                new StreamHandler(
                    Init::getContainer()
                        ->get(StuConfigInterface::class)
                        ->getDebugSettings()
                        ->getLogfilePath()
                ),
            );
        }

        return self::$logger;
    }
}
