<?php

declare(strict_types=1);

namespace Stu\Component\Logging\Sql;

use Monolog\Level;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

final class SqlLogger extends AbstractLogger
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param mixed[] $context
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->log(Level::Info, print_r(func_get_args(), true));
    }
}
