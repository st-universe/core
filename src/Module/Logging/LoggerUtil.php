<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Override;
use RuntimeException;
use Stu\Module\Config\StuConfigInterface;

final class LoggerUtil implements LoggerUtilInterface
{
    private ?Logger $logger = null;

    private int $level;

    private bool $doLog = false;

    public function __construct(private StuConfigInterface $stuConfig) {}

    #[Override]
    public function init(string $channel = 'stu', int $level = LoggerEnum::LEVEL_INFO): void
    {
        $this->level = $level;

        if ($this->checkDoLog()) {
            $this->logger = new Logger($channel);
            $this->logger->pushHandler(
                new StreamHandler(
                    $this->stuConfig->getDebugSettings()->getLogfilePath()
                ),
            );
        }
    }

    private function checkDoLog(): bool
    {
        $threshold = $this->stuConfig->getDebugSettings()->getLoglevel();

        $this->doLog = $threshold <= $this->level;

        return $this->doLog;
    }

    #[Override]
    public function doLog(): bool
    {
        return $this->doLog;
    }

    #[Override]
    public function log(string $message): void
    {
        if ($this->doLog) {
            if ($this->logger === null) {
                throw new RuntimeException('logger has not been initialized');
            }

            $method = LoggerEnum::LEVEL_METHODS[$this->level];
            $this->logger->$method($message);
        }
    }

    #[Override]
    public function logf(string $information, ...$args): void
    {
        $this->log(vsprintf(
            $information,
            $args
        ));
    }
}
