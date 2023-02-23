<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Monolog\Level;
use Stu\Game\GameRequestInterface;

abstract class AbstractAdapter implements GameRequstLoggerInterface
{
    public function info(
        GameRequestInterface $gameRequest
    ): void {
        $this->log(
            $gameRequest,
            Level::Info
        );
    }

    public function error(
        GameRequestInterface $gameRequest
    ): void {
        $this->log(
            $gameRequest,
            Level::Error
        );
    }

    /**
     * Does the actual logging, depending on the provided logging adapter
     */
    abstract protected function log(GameRequestInterface $gameRequest, Level $logLevel): void;
}
