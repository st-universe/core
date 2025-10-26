<?php

declare(strict_types=1);

namespace Stu\Component\Logging\Sql;

use Monolog\Level;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Stringable;

final class SqlLogger extends AbstractLogger
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * @param mixed[] $context
     */
    #[\Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->logger->log(Level::Info, print_r(func_get_args(), true));
    }
}
