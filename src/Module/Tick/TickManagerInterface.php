<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

interface TickManagerInterface
{
    public function work(): void;
}
