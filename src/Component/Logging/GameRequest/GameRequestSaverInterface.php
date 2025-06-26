<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Stu\Orm\Entity\GameRequest;

interface GameRequestSaverInterface
{
    /**
     * Save the request according to the internal configuration
     */
    public function save(
        GameRequest $gameRequest,
        bool $errorOccured = false
    ): void;
}
