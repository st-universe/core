<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

interface ProcessTickHandlerInterface
{
    public function work(): void;
}
