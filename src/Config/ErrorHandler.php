<?php

declare(strict_types=1);

namespace Stu\Config;

use Doctrine\DBAL\Connection;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Noodlehaus\ConfigInterface;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Throwable;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;

/**
 * Registers the main error handler
 */
final class ErrorHandler
{
    private Connection $database;

    private GameRequestSaverInterface $gameRequestSaver;

    private GameControllerInterface $game;

    private ConfigInterface $config;

    private SessionInterface $session;

    public function __construct(
        Connection $database,
        GameRequestSaverInterface $gameRequestSaver,
        GameControllerInterface $game,
        ConfigInterface $config,
        SessionInterface $session
    ) {
        $this->database = $database;
        $this->gameRequestSaver = $gameRequestSaver;
        $this->game = $game;
        $this->config = $config;
        $this->session = $session;
    }

    public function register(): void
    {
        $isAdminUser = false;
        // load the session handler only if a session has been started
        if (session_id() !== '') {
            $user = $this->session->getUser();

            $isAdminUser = $user !== null
                && in_array(
                    $user->getId(),
                    array_map('intval', $this->config->get('game.admins')),
                    true
                );
        }

        $whoops = new Run();

        if (
            $this->config->get('debug.debug_mode') === true ||
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
                    echo str_replace(
                        '$REQUESTID',
                        $this->game->getGameRequestId(),
                        (string) file_get_contents(__DIR__ . '/../html/error.html')
                    );
                };
            }
        }

        $whoops->prependHandler($handler);
        $whoops->prependHandler(function (): void {
            // end transaction if still active
            if ($this->database->isTransactionActive()) {
                $this->database->rollBack();
            }

            // save the game request
            $this->gameRequestSaver->save(
                $this->game->getGameRequest(),
                true
            );
        });

        $logger = new Logger('stu');
        $logger->pushHandler(
            new StreamHandler($this->config->get('debug.logfile_path'))
        );

        $whoops->prependHandler(function (Throwable $e) use ($logger) {
            $logger->error(
                $e->getMessage(),
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace(),
                ]
            );
        });
        $whoops->register();
    }
}
