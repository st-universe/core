<?php

namespace Stu\Module\Logging;

interface PirateLoggerInterface
{
    public function init(): void;

    public function log(string $message): void;

    /** @param string|int|float ...$args */
    public function logf(string $information, ...$args): void;
}
