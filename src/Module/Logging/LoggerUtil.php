<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use Monolog\Logger;
use Override;
use RuntimeException;
use Stu\Module\Config\StuConfigInterface;

final class LoggerUtil implements LoggerUtilInterface
{
    private ?Logger $logger = null;

    private LogLevelEnum $level;

    private bool $doLog = false;

    public function __construct(private StuConfigInterface $stuConfig) {}

    #[Override]
    public function init(string $channel = 'stu', LogLevelEnum $level = LogLevelEnum::INFO): void
    {
        $this->level = $level;

        if ($this->checkDoLog()) {
            $this->logger = StuLogger::getLogger(LogTypeEnum::DEFAULT);
        }
    }

    private function checkDoLog(): bool
    {
        $threshold = $this->stuConfig->getDebugSettings()->getLoglevel();

        $this->doLog = $threshold <= $this->level->value;

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

            $this->level->log($message, $this->logger);
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
