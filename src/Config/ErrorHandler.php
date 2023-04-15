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
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
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

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        Connection $database,
        GameRequestSaverInterface $gameRequestSaver,
        GameControllerInterface $game,
        StuConfigInterface $stuConfig,
        SessionInterface $session,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->database = $database;
        $this->gameRequestSaver = $gameRequestSaver;
        $this->game = $game;
        $this->stuConfig = $stuConfig;
        $this->session = $session;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function register(): void
    {
        $this->loggerUtil->init('ERRHAND', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log('A');

        $this->setErrorReporting();

        $whoops = new Run();
        $whoops->prependHandler(function () {

            $this->loggerUtil->log('D');

            if (
                $this->stuConfig->getDebugSettings()->isDebugMode()
                || $this->isAdminUser()
            ) {
                $this->loggerUtil->log('E');

                if (Misc::isCommandLine()) {
                    $this->loggerUtil->log('F');
                    $handler = new PlainTextHandler();
                } else {
                    $this->loggerUtil->log('G');
                    $handler = new PrettyPageHandler();
                    $handler->setPageTitle('Error - Star Trek Universe');
                }
            } else {
                $this->loggerUtil->log('H');
                if (Misc::isCommandLine()) {
                    $handler = new PlainTextHandler();
                } else {
                    $this->loggerUtil->log('I');
                    $handler = function (): void {
                        echo str_replace(
                            '$REQUESTID',
                            $this->game->getGameRequestId(),
                            (string) file_get_contents(__DIR__ . '/../html/error.html')
                        );
                    };
                }
            }

            $this->loggerUtil->log('J');

            return $handler;
        });
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
            new StreamHandler($this->stuConfig->getDebugSettings()->getLogfilePath())
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

    private function setErrorReporting(): void
    {
        if (
            $this->stuConfig->getDebugSettings()->isDebugMode()
            || $this->isAdminUser()
        ) {
            $this->loggerUtil->log('B');
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        } else {
            $this->loggerUtil->log('C');
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
