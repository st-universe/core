<?php

declare(strict_types=1);

namespace Stu\Config;

final class SessionStarter implements SessionStarterInterface
{
    #[\Override]
    public function start(): void
    {
        @session_start();
    }
}