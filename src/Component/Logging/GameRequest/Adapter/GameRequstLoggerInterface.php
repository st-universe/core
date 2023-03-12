<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Stu\Orm\Entity\GameRequestInterface;

interface GameRequstLoggerInterface
{
    /**
     * Logs the game request as info
     */
    public function info(
        GameRequestInterface $gameRequest
    ): void;

    /**
     * Logs the game request as error
     */
    public function error(
        GameRequestInterface $gameRequest
    ): void;
}