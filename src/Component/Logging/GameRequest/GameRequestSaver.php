<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Override;
use Stu\Component\Logging\GameRequest\Adapter\GameRequestLoggerInterface;
use Stu\Orm\Entity\GameRequest;

/**
 * Handles saving of the game's request
 */
final class GameRequestSaver implements GameRequestSaverInterface
{
    public function __construct(private ParameterSanitizerInterface $parameterSanitizer, private GameRequestLoggerInterface $gameRequstLogger)
    {
    }

    #[Override]
    public function save(
        GameRequest $gameRequest,
        bool $errorOccured = false
    ): void {
        $gameRequest = $this->parameterSanitizer->sanitize($gameRequest);

        $errorOccured
            ? $this->gameRequstLogger->error($gameRequest)
            : $this->gameRequstLogger->info($gameRequest);
    }
}
