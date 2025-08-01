<?php

namespace Stu\Module\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Stu\Config\Init;
use Stu\Module\Config\StuConfigInterface;

class StuLogger
{
    /** @var array<string, Logger> */
    private static array $loggers = [];

    public static function log(string $message, LogTypeEnum $type = LogTypeEnum::DEFAULT): void
    {
        LogLevelEnum::INFO->log($message, self::getLogger($type));
    }

    /** @param string|int|float $args */
    public static function logf(string $information, ...$args): void
    {
        self::log(vsprintf(
            $information,
            $args
        ));
    }

    public static function getLogger(LogTypeEnum $type): Logger
    {
        if (!array_key_exists($type->value, self::$loggers)) {

            $logPath = $type->getLogfilePath(Init::getContainer()->get(StuConfigInterface::class)
                ->getDebugSettings()->getLoggingSettings()->getLogDirectory());

            $logger = new Logger($type->value);

            if ($type->isRotating()) {
                $logger->pushHandler(
                    new RotatingFileHandler($logPath, 10, Level::Info)
                );
            } else {
                $logger->pushHandler(
                    new StreamHandler(
                        $logPath
                    ),
                );
            }

            self::$loggers[$type->value] = $logger;
        }

        return self::$loggers[$type->value];
    }
}
