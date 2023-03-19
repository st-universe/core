<?php

namespace Stu\Module\Tick\Process;

interface ProcessTickHandlerInterface
{
    public function work(): void;
}
