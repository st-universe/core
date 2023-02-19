<?php

declare(strict_types=1);

namespace Stu\Config;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Noodlehaus\ConfigInterface;
use Stu\Lib\SessionInterface;
use Throwable;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;

/**
 * Registers the main error handler
 */
final class ErrorHandler {
    public static function register(
        ConfigInterface $config,
        SessionInterface $session
    ): void {
        $isAdminUser = false;
        // load the session handler only if a session has been started
        if (session_id() !== '') {
            $user = $session->getUser();

            $isAdminUser = $user !== null
                && in_array(
                    $user->getId(),
                    array_map('intval', $config->get('game.admins')),
                    true
                );
        }

        $whoops = new Run();

        if (
            $config->get('debug.debug_mode') === true ||
            $isAdminUser
        ) {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

            if (Misc::isCommandLine()) {
                $handler = new PlainTextHandler();
            } else {
                $handler = new PrettyPageHandler();
                $handler->setPageTitle('Error - Star Trek Universe');
            }
        } else {
            error_reporting(E_ERROR | E_WARNING | E_PARSE);

            if (Misc::isCommandLine()) {
                $handler = new PlainTextHandler();
            } else {
                $handler = function (): void {
                    require_once __DIR__ . '/../html/error.html';
                };
            }
        }

        $whoops->prependHandler($handler);

        $logger = new Logger('stu');
        $logger->pushHandler(
            new StreamHandler($config->get('debug.logfile_path'))
        );

        $whoops->prependHandler(function (Throwable $e) use ($logger) {
            $logger->error(
                $e->getMessage(),
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ]
            );
        });
        $whoops->register();
    }
}