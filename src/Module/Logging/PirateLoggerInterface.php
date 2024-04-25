<?php

namespace Stu\Module\Logging;

interface PirateLoggerInterface
{
    public function initRotating(): void;

    public function log(string $message): void;

    /** @param string|int|float ...$args */
    public function logf(string $information, ...$args): void;
}
