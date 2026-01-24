<?php

namespace Stu\Module\Tick\History;

interface HistoryTickHandlerInterface
{
    public function work(): void;
}
