<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Monolog\Logger;
use Stu\Orm\Entity\GameRequestInterface;

abstract class AbstractAdapter implements GameRequstLoggerInterface
{
    public function info(
        GameRequestInterface $gameRequest
    ): void {
        $this->log(
            $gameRequest,
            Logger::INFO
        );
    }

    public function error(
        GameRequestInterface $gameRequest
    ): void {
        $this->log(
            $gameRequest,
            Logger::ERROR
        );
    }

    /**
     * Does the actual logging, depending on the provided logging adapter
     */
    abstract protected function log(GameRequestInterface $gameRequest, int $logLevel): void;
}
