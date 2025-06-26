<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Monolog\Level;
use Override;
use Psr\Log\LoggerInterface;
use Stu\Orm\Entity\GameRequest;
use Throwable;

/**
 * Writes the game request to a logfile
 */
final class LogfileAdapter extends AbstractAdapter
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    #[Override]
    protected function log(
        GameRequest $gameRequest,
        Level $logLevel,
        bool $isRequestCheck = true
    ): void {
        $this->logger->log(
            $logLevel,
            $gameRequest->getRequestId(),
            [
                'user_id' => $gameRequest->getUserId(),
                'module' => $gameRequest->getModule(),
                'action' => $gameRequest->getAction(),
                'view' => $gameRequest->getView(),
                'turn_id' => $gameRequest->getTurnId(),
                'timing' => [
                    'action' => $gameRequest->getActionMs(),
                    'view' => $gameRequest->getViewMs(),
                    'render' => $gameRequest->getRenderMs()
                ],
                'params' => $gameRequest->getParameterArray(),
                'sanity_errors' => array_map(
                    fn (Throwable $e): array => [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    $gameRequest->getErrors()
                ),
            ]
        );
    }
}
