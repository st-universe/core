<?php

declare(strict_types=1);

namespace Stu\Config;

use Doctrine\DBAL\Connection;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Config\StuConfigInterface;
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

    private StuConfigInterface $stuConfig;

    private SessionInterface $session;

    public function __construct(
        Connection $database,
        GameRequestSaverInterface $gameRequestSaver,
        GameControllerInterface $game,
        StuConfigInterface $stuConfig,
        SessionInterface $session
    ) {
        $this->database = $database;
        $this->gameRequestSaver = $gameRequestSaver;
        $this->game = $game;
        $this->stuConfig = $stuConfig;
        $this->session = $session;
    }

    public function register(bool $registerErrorHandlers): void
    {
        $this->setErrorReporting();

        if ($registerErrorHandlers) {
            $this->registerHandlers();
        }
    }

    private function registerHandlers(): void
    {
        $whoops = new Run();

        $whoops->appendHandler($this->outputHandler());
        $whoops->prependHandler($this->transactionHandler());
        $whoops->prependHandler($this->logfileHandler());

        $whoops->register();
    }

    private function outputHandler(): mixed
    {

        if (
            $this->stuConfig->getDebugSettings()->isDebugMode()
            || $this->isAdminUser()
        ) {
            if (Misc::isCommandLine()) {
                $handler = new PlainTextHandler();
            } else {
                $handler = new PrettyPageHandler();
                $handler->setPageTitle('Error - Star Trek Universe');
            }
        } elseif (Misc::isCommandLine()) {
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

        return $handler;
    }

    private function transactionHandler(): callable
    {
        return function (): void {

            // end transaction if still active
            if ($this->database->isTransactionActive()) {
                $this->database->rollBack();
            }

            // save the game request
            $this->gameRequestSaver->save(
                $this->game->getGameRequest(),
                true
            );
        };
    }

    private function logfileHandler(): callable
    {
        $logger = new Logger('stu');
        $logger->pushHandler(
            new StreamHandler($this->stuConfig->getDebugSettings()->getLogfilePath())
        );

        return function (Throwable $e) use ($logger): void {
            $logger->error(
                $e->getMessage(),
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTrace()
                ]
            );
        };
    }

    private function setErrorReporting(): void
    {
        if (
            $this->stuConfig->getDebugSettings()->isDebugMode()
            || $this->isAdminUser()
        ) {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        } else {
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
        }
    }

    private function isAdminUser(): bool
    {
        $isAdminUser = false;

        // load the session handler only if a session has been started
        if (session_id() !== '') {

            $user = $this->session->getUser();

            $isAdminUser = $user !== null
                && in_array(
                    $user->getId(),
                    $this->stuConfig->getGameSettings()->getAdminIds(),
                    true
                );
        }

        return $isAdminUser;
    }
}
