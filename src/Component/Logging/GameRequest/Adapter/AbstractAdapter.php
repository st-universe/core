<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Override;
use Monolog\Level;
use Stu\Orm\Entity\GameRequestInterface;

abstract class AbstractAdapter implements GameRequestLoggerInterface
{
    #[Override]
    public function info(
        GameRequestInterface $gameRequest,
        bool $isRequestCheck = true
    ): void {
        $this->log(
            $gameRequest,
            Level::Info,
            $isRequestCheck
        );
    }

    #[Override]
    public function error(
        GameRequestInterface $gameRequest,
        bool $isRequestCheck = true
    ): void {
        $this->log(
            $gameRequest,
            Level::Error,
            $isRequestCheck
        );
    }

    /**
     * Does the actual logging, depending on the provided logging adapter
     */
    abstract protected function log(
        GameRequestInterface $gameRequest,
        Level $logLevel,
        bool $isRequestCheck
    ): void;
}
