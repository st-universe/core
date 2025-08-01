<?php

declare(strict_types=1);

namespace Stu\Component\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Stu\Component\Logging\GameRequest\Adapter\DatabaseAdapter;
use Stu\Component\Logging\GameRequest\Adapter\GameRequestLoggerInterface;
use Stu\Component\Logging\GameRequest\Adapter\LogfileAdapter;
use Stu\Component\Logging\GameRequest\GameRequestSaver;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Component\Logging\GameRequest\ParameterSanitizer;
use Stu\Module\Config\StuConfigInterface;

use function DI\autowire;

return [
    GameRequestSaverInterface::class => function (ContainerInterface $dic): GameRequestSaverInterface {
        $stuConfig = $dic->get(StuConfigInterface::class);

        $adapter = match ($stuConfig->getDebugSettings()->getLoggingSettings()->getGameRequestLoggingAdapter()) {
            'database' => $dic->get(DatabaseAdapter::class),
            default => $dic->get(LogfileAdapter::class),
        };

        return new GameRequestSaver(
            $dic->get(ParameterSanitizer::class),
            $adapter
        );
    },
    DatabaseAdapter::class => autowire(),
    LogfileAdapter::class => function (StuConfigInterface $stuConfig): GameRequestLoggerInterface {

        $logDirectory = $stuConfig->getDebugSettings()->getLoggingSettings()->getLogDirectory();
        $logger = new Logger(
            'GameRequestLogger',
            [
                new RotatingFileHandler(
                    sprintf(
                        '%s/gamerequest-error.log',
                        $logDirectory,
                    ),
                    10,
                    Level::Error,
                    false
                ),
                new RotatingFileHandler(
                    sprintf(
                        '%s/gamerequest-info.log',
                        $logDirectory,
                    ),
                    10,
                    Level::Info,
                ),
            ]
        );

        return new LogfileAdapter(
            $logger
        );
    }
];
