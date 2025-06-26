<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Monolog\Level;
use Override;
use Stu\Orm\Entity\GameRequest;

abstract class AbstractAdapter implements GameRequestLoggerInterface
{
    #[Override]
    public function info(
        GameRequest $gameRequest,
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
        GameRequest $gameRequest,
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
        GameRequest $gameRequest,
        Level $logLevel,
        bool $isRequestCheck
    ): void;
}
