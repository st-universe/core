<?php

declare(strict_types=1);

use Noodlehaus\ConfigInterface;
use Stu\Lib\SessionInterface;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/** @var ConfigInterface $container */
$config = $container->get(ConfigInterface::class);

$isAdminUser = false;
// load the session handler only if a session has been started
if (session_id() !== '') {
    $user = $container->get(SessionInterface::class)->getUser();

    $isAdminUser = $user !== null && $user->isAdmin() === true;
}

$whoops = new Run();

if (
    $config->get('debug.debug_mode') === true ||
    $isAdminUser
) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

    if (Whoops\Util\Misc::isCommandLine()) {
        $handler = new PlainTextHandler();
    } else {
        $handler = new PrettyPageHandler();
        $handler->setPageTitle('Error - Star Trek Universe');
    }
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);

    if (Whoops\Util\Misc::isCommandLine()) {
        $handler = new PlainTextHandler();
    } else {
        $handler = function (): void {
            require_once __DIR__ . '/../html/error.html';
        };
    }
}

$whoops->prependHandler($handler);

$logger = new Monolog\Logger('stu');
$logger->pushHandler(
    new Monolog\Handler\StreamHandler($config->get('debug.logfile_path'))
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
