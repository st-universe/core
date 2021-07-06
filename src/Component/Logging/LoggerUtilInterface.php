<?php

declare(strict_types=1);

namespace Stu\Component\Logging;

interface LoggerUtilInterface
{
    public function init(string $channel = 'stu', int $level = LoggerEnum::LEVEL_INFO): void;

    public function log(string $message): void;
}
