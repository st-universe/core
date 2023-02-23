<?php

declare(strict_types=1);

namespace Stu\Component\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Noodlehaus\ConfigInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Logging\GameRequest\Adapter\GameRequstLoggerInterface;
use Stu\Component\Logging\GameRequest\Adapter\LogfileAdapter;
use Stu\Component\Logging\GameRequest\GameRequestSaver;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Component\Logging\GameRequest\ParameterSanitizer;

return [
    GameRequestSaverInterface::class => function (ContainerInterface $dic): GameRequestSaverInterface {
        return new GameRequestSaver(
            $dic->get(ParameterSanitizer::class),
            $dic->get(LogfileAdapter::class),
        );
    },
    LogfileAdapter::class => function (ConfigInterface $config): GameRequstLoggerInterface {
        $logger = new Logger(
            'GameRequestLogger',
            [
                new RotatingFileHandler(
                    sprintf(
                        '%s/gamerequest-error.log',
                        $config->get('debug.logging.log_dir'),
                    ),
                    10,
                    Level::Error,
                    false
                ),
                new RotatingFileHandler(
                    sprintf(
                        '%s/gamerequest-info.log',
                        $config->get('debug.logging.log_dir'),
                    ),
                    10,
                    Level::Info,
                ),
            ]
        );

        return new LogfileAdapter(
            $logger
        );
    },
];
