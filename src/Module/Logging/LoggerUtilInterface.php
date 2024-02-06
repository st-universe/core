<?php

namespace Stu\Module\Logging;

interface LoggerUtilInterface
{
    public function init(string $channel = 'stu', int $level = LoggerEnum::LEVEL_INFO): void;

    public function doLog(): bool;

    public function log(string $message): void;

    /** @param string|int|float $args */
    public function logf(string $information, ...$args): void;
}
