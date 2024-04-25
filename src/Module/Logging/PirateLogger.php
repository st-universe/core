<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Stu\Module\Config\StuConfigInterface;

final class PirateLogger implements PirateLoggerInterface
{
    private ?Logger $logger = null;

    public function __construct(private StuConfigInterface $stuConfig)
    {
    }

    public function initRotating(): void
    {
        $this->logger = new Logger('KAZON');
        $this->logger->pushHandler(
            new RotatingFileHandler(
                $this->stuConfig->getGameSettings()->getPirateLogfilePath()
            ),
        );
    }

    public function log(string $message): void
    {
        $method = LoggerEnum::LEVEL_METHODS[LoggerEnum::LEVEL_INFO];
        $this->logger->$method($message);
    }

    public function logf(string $information, ...$args): void
    {
        $this->log(vsprintf(
            $information,
            $args
        ));
    }
}
