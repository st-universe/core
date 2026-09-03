<?php

declare(strict_types=1);

namespace Stu\Module\Tick\History;

interface HistoryTickHandlerInterface
{
    public function work(): void;
}
