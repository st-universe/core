<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use JBBCode\Parser;
use Monolog\Logger;
use Override;
use RuntimeException;

final class PirateLogger implements PirateLoggerInterface
{
    private ?Logger $logger = null;

    public function __construct(
        private Parser $parser
    ) {}

    #[Override]
    public function init(): void
    {
        $this->logger = StuLogger::getLogger(LogTypeEnum::PIRATE);
    }

    #[Override]
    public function log(string $message): void
    {
        if ($this->logger === null) {
            throw new RuntimeException('logger has not been initialized');
        }

        LogLevelEnum::INFO->log($this->parser->parse($message)->getAsText(), $this->logger);
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
