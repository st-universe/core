<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Stu\Game\GameRequestInterface;

interface GameRequestSaverInterface
{
    /**
     * Save the request according to the internal configuration
     */
    public function save(
        GameRequestInterface $gameRequest,
        bool $errorOccured = false
    ): void;
}
