<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Stu\Orm\Entity\GameRequest;

interface GameRequestLoggerInterface
{
    /**
     * Logs the game request as info
     */
    public function info(
        GameRequest $gameRequest,
        bool $isRequestCheck = true
    ): void;

    /**
     * Logs the game request as error
     */
    public function error(
        GameRequest $gameRequest,
        bool $isRequestCheck = true
    ): void;
}
