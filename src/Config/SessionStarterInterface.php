<?php

declare(strict_types=1);

namespace Stu\Config;

interface SessionStarterInterface
{
    public function start(): void;
}