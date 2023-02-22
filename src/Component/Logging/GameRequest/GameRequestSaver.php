<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Stu\Component\Logging\GameRequest\Adapter\GameRequstLoggerInterface;
use Stu\Orm\Entity\GameRequestInterface;

/**
 * Handles saving of the game's request
 */
final class GameRequestSaver implements GameRequestSaverInterface
{
    private GameRequstLoggerInterface $gameRequstLogger;

    private ParameterSanitizerInterface $parameterSanitizer;

    public function __construct(
        ParameterSanitizerInterface $parameterSanitizer,
        GameRequstLoggerInterface $gameRequstLogger
    ) {
        $this->gameRequstLogger = $gameRequstLogger;
        $this->parameterSanitizer = $parameterSanitizer;
    }

    public function save(
        GameRequestInterface $gameRequest,
        bool $errorOccured = false
    ): void {
        $gameRequest = $this->parameterSanitizer->sanitize($gameRequest);

        $errorOccured
            ? $this->gameRequstLogger->error($gameRequest)
            : $this->gameRequstLogger->info($gameRequest);
    }
}
